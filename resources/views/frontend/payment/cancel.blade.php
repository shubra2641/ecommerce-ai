@extends('frontend.layouts.master')

@section('title', 'Payment Cancelled')

@section('main-content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-warning text-dark">
                    <h4 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> {{ __('Payment Cancelled') }}
                    </h4>
                </div>
                <div class="card-body text-center">
                    <div class="mb-4">
                        <i class="fas fa-times-circle text-warning" style="font-size: 4rem;"></i>
                    </div>
                    
                    <h5 class="text-warning mb-3">{{ __('Payment was not completed') }}</h5>
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-info-circle"></i>
                        {{ __('Your payment was cancelled or failed. No charges have been made to your account.') }}
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('cart') }}" class="btn btn-primary">
                            <i class="fas fa-shopping-cart"></i> {{ __('Back to Cart') }}
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
