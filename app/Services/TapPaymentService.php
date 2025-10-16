<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TapPaymentService
{
    protected $secretKey;
    protected $publishableKey;
    protected $baseUrl;
    protected $testMode;

    public function __construct()
    {
        $gatewayConfig = PaymentGateway::where('slug', 'tap')->first();
        
        if ($gatewayConfig) {
            $this->testMode = $gatewayConfig->mode === 'test';
            $this->baseUrl = 'https://api.tap.company/v2';
            
            $credentials = $gatewayConfig->credentials;
            $mode = $gatewayConfig->mode;
            
            $this->secretKey = $credentials[$mode]['secret_key'] ?? '';
            $this->publishableKey = $credentials[$mode]['publishable_key'] ?? '';
        } else {
            // Fallback to environment variables
            $this->testMode = true;
            $this->baseUrl = 'https://api.tap.company/v2';
            $this->secretKey = env('TAP_SECRET_KEY', '');
            $this->publishableKey = env('TAP_PUBLISHABLE_KEY', '');
        }
    }

    public function createCharge(Order $order)
    {
        try {
            $chargeData = [
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'customer' => [
                    'first_name' => $order->first_name,
                    'last_name' => $order->last_name,
                    'email' => $order->email,
                    'phone' => [
                        'country_code' => '+1',
                        'number' => $order->phone
                    ]
                ],
                'source' => [
                    'id' => 'src_all'
                ],
                'redirect' => [
                    'url' => route('payment.success', ['order_id' => $order->id, 'gateway' => 'tap'])
                ],
                'post' => [
                    'url' => route('payment.webhook', ['gateway' => 'tap'])
                ],
                'reference' => [
                    'transaction' => $order->order_number
                ],
                'description' => "Order #{$order->order_number}",
                'metadata' => [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json'
            ])->post($this->baseUrl . '/charges', $chargeData);

            if ($response->successful()) {
                $charge = $response->json();
                
                // Update order with transaction ID
                $order->update([
                    'transaction_id' => $charge['id'],
                    'payment_status' => 'pending'
                ]);

                return [
                    'success' => true,
                    'redirect_url' => $charge['transaction']['url'] ?? null,
                    'transaction_id' => $charge['id'],
                    'gateway_response' => $charge
                ];
            } else {
                Log::error('TAP Payment Error', [
                    'order_id' => $order->id,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);

                return [
                    'success' => false,
                    'message' => $response->json('message', 'TAP payment failed'),
                    'gateway_response' => $response->json()
                ];
            }
        } catch (\Exception $e) {
            Log::error('TAP Payment Exception', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function handleWebhook(array $data)
    {
        try {
            $chargeId = $data['id'] ?? null;
            $status = $data['status'] ?? null;

            if (!$chargeId) {
                return ['success' => false, 'message' => 'Invalid webhook data'];
            }

            // Find order by transaction ID
            $order = Order::where('transaction_id', $chargeId)->first();
            if (!$order) {
                return ['success' => false, 'message' => 'Order not found'];
            }

            if ($status === 'CAPTURED') {
                // Payment successful
                $order->update([
                    'status' => 'completed',
                    'payment_status' => 'paid',
                    'payment_method' => 'tap'
                ]);

                // Clear cart
                \App\Models\Cart::where('user_id', $order->user_id)->delete();

                return ['success' => true, 'message' => 'Payment completed successfully'];
            } elseif (in_array($status, ['FAILED', 'CANCELLED', 'ABANDONED'])) {
                // Payment failed
                $order->update([
                    'status' => 'cancelled',
                    'payment_status' => 'failed'
                ]);

                return ['success' => false, 'message' => 'Payment failed'];
            }

            return ['success' => true, 'message' => 'Webhook processed'];
        } catch (\Exception $e) {
            Log::error('TAP Webhook Exception', [
                'data' => $data,
                'message' => $e->getMessage()
            ]);

            return ['success' => false, 'message' => 'Webhook processing failed'];
        }
    }
}
