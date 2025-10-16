@extends('frontend.layouts.master')

@section('title', 'Payment Success')

@section('main-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-check-circle"></i> {{ __('Payment Successful') }}
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="text-success mb-3">{{ __('Thank you for your purchase!') }}</h5>
                    
                    @if(isset($order))
                    <div class="order-details mb-4">
                        <h6>{{ __('Order Details') }}</h6>
                        <p><strong>{{ __('Order Number') }}:</strong> {{ $order->order_number }}</p>
                        <p><strong>{{ __('Total Amount') }}:</strong> ${{ number_format($order->total_amount, 2) }}</p>
                        <p><strong>{{ __('Payment Method') }}:</strong> {{ ucfirst($order->payment_method) }}</p>
                        <p><strong>{{ __('Status') }}:</strong> 
                            <span class="badge badge-success">{{ ucfirst($order->status) }}</span>
                        </p>
                    </div>
                    @endif

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        {{ __('You will receive an email confirmation shortly.') }}
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('user.order.index') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> {{ __('View My Orders') }}
                        </a>
                        <a href="{{ route('home') }}" class="btn btn-secondary">
                            <i class="fas fa-home"></i> {{ __('Continue Shopping') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
