<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Cart;
use App\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;
use Omnipay\Omnipay;
use Omnipay\Common\Message\ResponseInterface;

class OmnipayPaymentService
{
    protected $gateway;
    protected $gatewayName;

    public function __construct($gatewayName = 'paypal')
    {
        $this->gatewayName = $gatewayName;
        $this->initializeGateway();
    }

    protected function initializeGateway()
    {
        $gatewayConfig = PaymentGateway::where('slug', $this->gatewayName)->first();
        
        if (!$gatewayConfig) {
            throw new \InvalidArgumentException("Gateway configuration not found: {$this->gatewayName}");
        }

        switch ($this->gatewayName) {
            case 'paypal':
                $this->gateway = Omnipay::create('PayPal_Rest');
                $credentials = $gatewayConfig->credentials;
                $mode = $gatewayConfig->mode;
                
                $this->gateway->initialize([
                    'clientId' => $credentials[$mode]['client_id'] ?? '',
                    'secret' => $credentials[$mode]['client_secret'] ?? '',
                    'testMode' => $mode === 'test' || $mode === 'sandbox',
                ]);
                break;

            case 'stripe':
                $this->gateway = Omnipay::create('Stripe');
                $credentials = $gatewayConfig->credentials;
                $mode = $gatewayConfig->mode;
                
                $this->gateway->initialize([
                    'apiKey' => $credentials[$mode]['secret_key'] ?? '',
                    'testMode' => $mode === 'test',
                ]);
                break;

            case 'tap':
                // TAP uses custom service, not Omnipay
                $this->gateway = null;
                break;

            default:
                throw new \InvalidArgumentException("Unsupported gateway: {$this->gatewayName}");
        }
    }

    public function processPayment(Order $order, array $data = [])
    {
        try {
            Log::info('Processing payment with Omnipay', [
                'order_id' => $order->id,
                'gateway' => $this->gatewayName,
                'amount' => $order->total_amount
            ]);

            // Handle TAP payments separately
            if ($this->gatewayName === 'tap') {
                $tapService = new \App\Services\TapPaymentService();
                return $tapService->createCharge($order);
            }

            $purchaseData = [
                'amount' => $order->total_amount,
                'currency' => 'USD',
                'description' => "Order #{$order->order_number}",
                'returnUrl' => route('payment.success', ['order_id' => $order->id, 'gateway' => $this->gatewayName]),
                'cancelUrl' => route('payment.cancel', ['order_id' => $order->id, 'gateway' => $this->gatewayName]),
            ];

            // Add gateway-specific parameters
            if ($this->gatewayName === 'stripe') {
                $purchaseData['source'] = 'tok_visa'; // Test token for Stripe
            }

            $response = $this->gateway->purchase($purchaseData)->send();

            if ($response->isSuccessful()) {
                // Payment was successful
                $this->handleSuccessfulPayment($order, $response);
                return [
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $response->getTransactionReference(),
                    'redirect_url' => null
                ];
            } elseif ($response->isRedirect()) {
                // Redirect to offsite payment gateway
                return [
                    'success' => true,
                    'message' => 'Redirecting to payment gateway',
                    'transaction_id' => $response->getTransactionReference(),
                    'redirect_url' => $response->getRedirectUrl()
                ];
            } else {
                // Payment failed
                Log::error('Payment failed', [
                    'order_id' => $order->id,
                    'gateway' => $this->gatewayName,
                    'message' => $response->getMessage(),
                    'code' => $response->getCode()
                ]);

                return [
                    'success' => false,
                    'message' => $response->getMessage(),
                    'transaction_id' => $response->getTransactionReference(),
                    'redirect_url' => null
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment processing exception', [
                'order_id' => $order->id,
                'gateway' => $this->gatewayName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Payment processing failed: ' . $e->getMessage(),
                'transaction_id' => null,
                'redirect_url' => null
            ];
        }
    }

    public function handleCallback(array $data)
    {
        try {
            // Handle TAP webhook separately
            if ($this->gatewayName === 'tap') {
                $tapService = new \App\Services\TapPaymentService();
                return $tapService->handleWebhook($data);
            }

            $response = $this->gateway->completePurchase($data)->send();

            if ($response->isSuccessful()) {
                $transactionId = $response->getTransactionReference();
                $orderId = $data['order_id'] ?? null;

                if ($orderId) {
                    $order = Order::find($orderId);
                    if ($order) {
                        $this->handleSuccessfulPayment($order, $response);
                    }
                }

                return [
                    'success' => true,
                    'message' => 'Payment completed successfully',
                    'transaction_id' => $transactionId
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->getMessage(),
                    'transaction_id' => $response->getTransactionReference()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Payment callback exception', [
                'gateway' => $this->gatewayName,
                'error' => $e->getMessage(),
                'data' => $data
            ]);

            return [
                'success' => false,
                'message' => 'Payment callback failed: ' . $e->getMessage(),
                'transaction_id' => null
            ];
        }
    }

    protected function handleSuccessfulPayment(Order $order, ResponseInterface $response)
    {
        // Update order status
        $order->update([
            'status' => 'completed',
            'payment_status' => 'paid',
            'payment_method' => $this->gatewayName,
            'transaction_id' => $response->getTransactionReference(),
        ]);

        // Clear cart
        Cart::where('user_id', $order->user_id)->delete();

        Log::info('Payment successful', [
            'order_id' => $order->id,
            'gateway' => $this->gatewayName,
            'transaction_id' => $response->getTransactionReference()
        ]);
    }

    public function refund(Order $order, $amount = null)
    {
        try {
            $refundAmount = $amount ?? $order->total_amount;

            $response = $this->gateway->refund([
                'transactionReference' => $order->transaction_id,
                'amount' => $refundAmount,
            ])->send();

            if ($response->isSuccessful()) {
                $order->update([
                    'payment_status' => 'refunded',
                    'status' => 'cancelled'
                ]);

                return [
                    'success' => true,
                    'message' => 'Refund processed successfully',
                    'transaction_id' => $response->getTransactionReference()
                ];
            } else {
                return [
                    'success' => false,
                    'message' => $response->getMessage(),
                    'transaction_id' => $response->getTransactionReference()
                ];
            }
        } catch (\Exception $e) {
            Log::error('Refund exception', [
                'order_id' => $order->id,
                'gateway' => $this->gatewayName,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'message' => 'Refund failed: ' . $e->getMessage(),
                'transaction_id' => null
            ];
        }
    }

    public static function create($gatewayName)
    {
        return new self($gatewayName);
    }
}
