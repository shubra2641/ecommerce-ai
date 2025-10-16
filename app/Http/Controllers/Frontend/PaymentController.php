<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OmnipayPaymentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function process(Request $request)
    {
        $orderId = $request->get('order_id');
        $gateway = $request->get('gateway', 'paypal');

        if (!$orderId) {
            return redirect()->route('cart')->with('error', 'Order not found');
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->where('status', 'new')
            ->first();

        if (!$order) {
            return redirect()->route('cart')->with('error', 'Order not found or already processed');
        }

        try {
            $paymentService = OmnipayPaymentService::create($gateway);
            $result = $paymentService->processPayment($order);

            if ($result['success']) {
                if ($result['redirect_url']) {
                    // Redirect to payment gateway
                    return redirect($result['redirect_url']);
                } else {
                    // Payment completed immediately
                    return redirect()->route('payment.success', ['order_id' => $order->id])
                        ->with('success', $result['message']);
                }
            } else {
                return redirect()->route('payment.cancel', ['order_id' => $order->id])
                    ->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            Log::error('Payment processing error', [
                'order_id' => $orderId,
                'gateway' => $gateway,
                'error' => $e->getMessage()
            ]);

            return redirect()->route('payment.cancel', ['order_id' => $order->id])
                ->with('error', 'Payment processing failed. Please try again.');
        }
    }

    public function success(Request $request)
    {
        $orderId = $request->get('order_id');
        $gateway = $request->get('gateway', 'paypal');

        if (!$orderId) {
            return redirect()->route('cart')->with('error', 'Order not found');
        }

        $order = Order::where('id', $orderId)
            ->where('user_id', auth()->id())
            ->first();

        if (!$order) {
            return redirect()->route('cart')->with('error', 'Order not found');
        }

        // Handle callback for redirect-based payments
        if ($request->has('PayerID') || $request->has('token')) {
            try {
                $paymentService = OmnipayPaymentService::create($gateway);
                $result = $paymentService->handleCallback($request->all());

                if (!$result['success']) {
                    return redirect()->route('payment.cancel', ['order_id' => $order->id])
                        ->with('error', $result['message']);
                }
            } catch (\Exception $e) {
                Log::error('Payment callback error', [
                    'order_id' => $orderId,
                    'gateway' => $gateway,
                    'error' => $e->getMessage()
                ]);

                return redirect()->route('payment.cancel', ['order_id' => $order->id])
                    ->with('error', 'Payment verification failed.');
            }
        }

        return view('frontend.payment.success', compact('order'));
    }

    public function cancel(Request $request)
    {
        $orderId = $request->get('order_id');

        if ($orderId) {
            $order = Order::where('id', $orderId)
                ->where('user_id', auth()->id())
                ->first();

            if ($order && $order->status === 'pending') {
                // Optionally delete the order or keep it for retry
                // $order->delete();
            }
        }

        return view('frontend.payment.cancel');
    }

    public function webhook(Request $request, $gateway)
    {
        try {
            $paymentService = OmnipayPaymentService::create($gateway);
            $result = $paymentService->handleCallback($request->all());

            if ($result['success']) {
                return response()->json(['status' => 'success']);
            } else {
                return response()->json(['status' => 'error', 'message' => $result['message']], 400);
            }
        } catch (\Exception $e) {
            Log::error('Webhook error', [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return response()->json(['status' => 'error', 'message' => 'Webhook processing failed'], 500);
        }
    }
}
