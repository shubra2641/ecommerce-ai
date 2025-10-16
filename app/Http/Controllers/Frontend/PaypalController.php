<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use Srmklive\PayPal\Services\ExpressCheckout;
use App\Models\Cart;
use App\Models\Product;
use App\Models\PaymentGateway;
use Exception;

/**
 * PaypalController handles PayPal payment processing
 * 
 * This controller manages PayPal payment initialization, success handling,
 * cancellation, and payment gateway configuration with secure validation
 * and proper error handling.
 */
class PaypalController extends Controller
{
    /**
     * Initialize PayPal payment process
     * 
     * @return RedirectResponse
     */
    public function payment(): RedirectResponse
    {
        try {
            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Authentication required');
                return redirect()->route('login');
            }

            // Get user's cart items
            $cart = Cart::where('user_id', Auth::id())
                ->where('order_id', null)
                ->get();

            if ($cart->isEmpty()) {
                request()->session()->flash('error', 'Your cart is empty');
                return redirect()->route('cart.index');
            }

            // Prepare payment data
            $data = $this->preparePaymentData($cart);

            // Update cart items with order ID
            $orderId = session()->get('id');
            if ($orderId) {
                Cart::where('user_id', Auth::id())
                    ->where('order_id', null)
                    ->update(['order_id' => $orderId]);
            }

            // Create PayPal provider
            $provider = $this->makePaypalProviderFromDb();

            // Set up Express Checkout
            $response = $provider->setExpressCheckout($data);

            if (isset($response['paypal_link'])) {
                return redirect($response['paypal_link']);
            } else {
                throw new Exception('Failed to initialize PayPal payment');
            }

        } catch (Exception $e) {
            \Log::error('Error initializing PayPal payment: ' . $e->getMessage(), [
                'user_id' => Auth::id()
            ]);
            request()->session()->flash('error', 'An error occurred while processing your payment');
            return redirect()->back();
        }
    }

    /**
     * Prepare payment data for PayPal
     * 
     * @param \Illuminate\Database\Eloquent\Collection $cart
     * @return array
     */
    private function preparePaymentData($cart): array
    {
        $data = [];
        
        // Prepare cart items
        $data['items'] = $cart->map(function ($item) {
            $product = Product::find($item->product_id);
            $name = $product ? $product->title : 'Product';
            
            return [
                'name' => $name,
                'price' => $item->price,
                'desc' => 'Thank you for using PayPal',
                'qty' => $item->quantity
            ];
        })->toArray();

        // Generate invoice ID
        $data['invoice_id'] = 'ORD-' . strtoupper(uniqid());
        $data['invoice_description'] = "Order #{$data['invoice_id']} Invoice";
        $data['return_url'] = route('payment.success');
        $data['cancel_url'] = route('payment.cancel');

        // Calculate total
        $total = 0;
        foreach ($data['items'] as $item) {
            $total += $item['price'] * $item['qty'];
        }
        $data['total'] = $total;

        // Apply coupon discount if available
        if (session('coupon')) {
            $data['shipping_discount'] = session('coupon')['value'];
        }

        return $data;
    }
   
    /**
     * Handle PayPal payment cancellation
     * 
     * @return RedirectResponse
     */
    public function cancel(): RedirectResponse
    {
        try {
            request()->session()->flash('warning', 'Your payment was canceled. You can try again or choose a different payment method.');
            return redirect()->route('cart.index');
            
        } catch (Exception $e) {
            \Log::error('Error handling payment cancellation: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while processing your cancellation');
            return redirect()->route('home');
        }
    }
  
    /**
     * Handle successful PayPal payment
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function success(\App\Http\Requests\Frontend\PaypalSuccessRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Check if user is authenticated
            if (!Auth::check()) {
                request()->session()->flash('error', 'Authentication required');
                return redirect()->route('login');
            }

            $provider = $this->makePaypalProviderFromDb();
            $response = $provider->getExpressCheckoutDetails($validatedData['token']);

            if (in_array(strtoupper($response['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
                // Complete the payment
                $paymentResponse = $provider->doExpressCheckoutPayment($validatedData, $validatedData['PayerID']);
                
                if (in_array(strtoupper($paymentResponse['ACK']), ['SUCCESS', 'SUCCESSWITHWARNING'])) {
                    // Clear cart and coupon sessions
                    session()->forget(['cart', 'coupon']);
                    
                    request()->session()->flash('success', 'You successfully paid with PayPal! Thank you for your purchase.');
                    return redirect()->route('home');
                } else {
                    throw new Exception('Payment completion failed: ' . ($paymentResponse['L_LONGMESSAGE0'] ?? 'Unknown error'));
                }
            } else {
                throw new Exception('Payment verification failed: ' . ($response['L_LONGMESSAGE0'] ?? 'Unknown error'));
            }

        } catch (Exception $e) {
            \Log::error('Error processing PayPal payment success: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'token' => $request->input('token')
            ]);
            request()->session()->flash('error', 'Something went wrong with your payment. Please try again or contact support.');
            return redirect()->back();
        }
    }

    /**
     * Build an ExpressCheckout provider configured from DB-stored gateway credentials
     * 
     * @return ExpressCheckout
     */
    protected function makePaypalProviderFromDb(): ExpressCheckout
    {
        try {
            $gateway = PaymentGateway::where('slug', 'paypal')
                ->where('enabled', true)
                ->first();

            if ($gateway && $gateway->credentials && is_array($gateway->credentials)) {
                $creds = $gateway->credentials;

                // Map expected keys to package config structure
                $config = [
                    'mode' => $gateway->mode ?? 'sandbox',
                    'sandbox' => [
                        'client_id' => $this->getCredentialValue($creds, ['client_id', 'clientID', 'clientId']),
                        'client_secret' => $this->getCredentialValue($creds, ['client_secret', 'clientSecret', 'secret']),
                    ],
                    'live' => [
                        'client_id' => $this->getCredentialValue($creds, ['live_client_id', 'client_id_live']),
                        'client_secret' => $this->getCredentialValue($creds, ['live_client_secret', 'client_secret_live']),
                    ],
                ];

                // Validate configuration
                if ($this->isValidPaypalConfig($config)) {
                    return new ExpressCheckout($config);
                } else {
                    \Log::warning('Invalid PayPal configuration found in database');
                }
            } else {
                \Log::info('No PayPal gateway configuration found in database, using default config');
            }
        } catch (Exception $e) {
            \Log::error('Error loading PayPal configuration from database: ' . $e->getMessage());
        }

        // Fallback to default configuration
        return new ExpressCheckout();
    }

    /**
     * Get credential value from array with multiple possible keys
     * 
     * @param array $creds
     * @param array $keys
     * @return string|null
     */
    private function getCredentialValue(array $creds, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($creds[$key]) && !empty($creds[$key])) {
                return $creds[$key];
            }
        }
        return null;
    }

    /**
     * Validate PayPal configuration
     * 
     * @param array $config
     * @return bool
     */
    private function isValidPaypalConfig(array $config): bool
    {
        $mode = $config['mode'] ?? 'sandbox';
        $modeConfig = $config[$mode] ?? [];

        return !empty($modeConfig['client_id']) && !empty($modeConfig['client_secret']);
    }
}