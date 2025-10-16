@extends('frontend.layouts.master')

@section('title','Checkout page')

@section('main-content')

    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2 class="breadcrumb-title">{{ __('Checkout') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout -->
    <section class="checkout-section section">
        <div class="container">
            <form action="{{ route('cart.order') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row">
                    <div class="col-lg-8 col-12">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h4 class="card-title mb-3">{{ __('Billing Details') }}</h4>
                                <div class="row">
                                    {{-- ...existing billing fields... --}}
                                    @includeWhen(true, 'frontend.pages.partials.checkout-billing', [])
                                </div>
                            </div>
                        </div>

                        <div class="card mb-3">
                            <div class="card-body">
                                <h4 class="card-title mb-3">{{ __('Payment Method') }}</h4>
                                <div class="payment-methods">
                                    @if(isset($online_gateways) && $online_gateways->count())
                                        <div class="gateway-group mb-2">
                                            <h6 class="mb-2">{{ __('Online') }}</h6>
                                            <div class="row">
                                            @foreach($online_gateways as $gateway)
                                                <div class="col-12 mb-2">
                                                    <label class="gateway-card p-3 d-flex align-items-center rounded border online-gateway" for="gateway-{{ $gateway->slug }}">
                                                        <input class="form-check-input me-3 online-gateway" id="gateway-{{ $gateway->slug }}" name="payment_method" type="radio" value="{{ $gateway->slug }}">
                                                        <i class="{{ $gateway->icon_class }} me-2"></i>
                                                        <div>
                                                            <div class="gateway-name">{{ $gateway->display_name }}</div>
                                                            @if($gateway->description)
                                                                <small class="text-muted">{{ $gateway->description }}</small>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if(isset($offline_gateways) && $offline_gateways->count())
                                        <div class="gateway-group mb-2">
                                            <h6 class="mb-2">{{ __('Offline') }}</h6>
                                            <div class="row">
                                            @foreach($offline_gateways as $gateway)
                                                <div class="col-12 mb-2">
                                                    <label class="gateway-card p-3 d-flex align-items-center rounded border offline-gateway" for="gateway-{{ $gateway->slug }}">
                                                        <input class="form-check-input me-3 offline-gateway" id="gateway-{{ $gateway->slug }}" name="payment_method" type="radio" value="{{ $gateway->slug }}" data-require-proof="{{ $gateway->require_proof ? '1' : '0' }}" data-transfer-details="{{ e($gateway->transfer_details ?? '') }}">
                                                        <i class="{{ $gateway->icon_class }} me-2"></i>
                                                        <div>
                                                            <div class="gateway-name">{{ $gateway->display_name }}</div>
                                                            @if($gateway->description)
                                                                <small class="text-muted">{{ $gateway->description }}</small>
                                                            @endif
                                                            @if($gateway->require_proof)
                                                                <small class="text-warning d-block">{{ __('Requires payment proof') }}</small>
                                                            @endif
                                                        </div>
                                                    </label>
                                                </div>
                                            @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    @if((!isset($online_gateways) || !$online_gateways->count()) && (!isset($offline_gateways) || !$offline_gateways->count()))
                                        <div class="alert alert-warning">
                                            {{ __('Purchases are temporarily closed because no payment gateways are available at the moment.') }}
                                        </div>
                                    @endif

                                    <div id="gateway-info" class="mt-3 d-none">
                                        <div id="gateway-transfer-details" class="mb-2"></div>
                                        <div id="gateway-proof-upload" class="d-none">
                                            <label class="form-label">{{ __('Upload transfer proof') }}</label>
                                            <input type="file" name="payment_proof" accept="image/*" class="form-control">
                                            <small class="form-text text-muted">{{ __('Upload image of bank transfer or receipt if required by the gateway.') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <div class="card sticky-top">
                            <div class="card-body">
                                <h4 class="card-title">{{ __('Order Summary') }}</h4>
                                <ul class="list-unstyled mb-3">
                                    <li class="d-flex justify-content-between align-items-center"><span>{{ __('Subtotal') }}</span> <strong>${{ number_format(Helper::totalCartPrice(),2) }}</strong></li>
                                    <li class="d-flex justify-content-between align-items-center"><span>{{ __('Shipping') }}</span>
                                        <span>
                                            @if(count(Helper::shipping())>0 && Helper::cartCount()>0)
                                                <select name="shipping" class="nice-select form-select">
                                                    <option value="">{{ __('Select') }}</option>
                                                    @foreach(Helper::shipping() as $shipping)
                                                        <option value="{{$shipping->id}}" data-price="{{$shipping->price}}">{{$shipping->type}}: ${{$shipping->price}}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                {{ __('Free') }}
                                            @endif
                                        </span>
                                    </li>
                                    @if(session('coupon'))
                                        <li class="d-flex justify-content-between align-items-center"><span>{{ __('You Save') }}</span> <strong>${{ number_format(session('coupon')['value'],2) }}</strong></li>
                                    @endif
                                    <li class="d-flex justify-content-between align-items-center"><span>{{ __('Total') }}</span> <strong id="order_total_price">${{ number_format($total_amount,2) }}</strong></li>
                                </ul>

                                <div class="mb-3 text-center">
                                    <img src="{{ asset('backend/img/payment-method.png') }}" alt="" class="img-fluid">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary btn-block" {{ !$has_gateways ? 'disabled' : '' }}>{{ $has_gateways ? __('Proceed to checkout') : __('Checkout temporarily unavailable') }}</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!--/ End Checkout -->
    
    <!-- Start Shop Services Area  -->
    <section class="shop-services section home">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-rocket"></i>
                        <h4>{{ __('frontend.services.free_shipping') }}</h4>
                        <p>{{ __('frontend.services.free_shipping_sub') }}</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-reload"></i>
                        <h4>{{ __('frontend.services.free_return') }}</h4>
                        <p>{{ __('frontend.services.free_return_sub') }}</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-lock"></i>
                        <h4>{{ __('frontend.services.secure_payment') }}</h4>
                        <p>{{ __('frontend.services.secure_payment_sub') }}</p>
                    </div>
                    <!-- End Single Service -->
                </div>
                <div class="col-lg-3 col-md-6 col-12">
                    <!-- Start Single Service -->
                    <div class="single-service">
                        <i class="ti-tag"></i>
                        <h4>{{ __('frontend.services.best_price') }}</h4>
                        <p>{{ __('frontend.services.best_price_sub') }}</p>
                    </div>
                    <!-- End Single Service -->
                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Services -->
    
    <!-- Start Shop Newsletter  -->
    <section class="shop-newsletter section">
        <div class="container">
            <div class="inner-top">
                <div class="row">
                    <div class="col-lg-8 offset-lg-2 col-12">
                        <!-- Start Newsletter Inner -->
                        <div class="inner">
                            <h4>Newsletter</h4>
                            <p> Subscribe to our newsletter and get <span>10%</span> off your first purchase</p>
                            <form action="mail/mail.php" method="get" target="_blank" class="newsletter-inner">
                                <input name="EMAIL" placeholder="Your email address" required="" type="email">
                                <button class="btn">Subscribe</button>
                            </form>
                        </div>
                        <!-- End Newsletter Inner -->
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Shop Newsletter -->
@endsection
@push('styles')
	<style>
        .gateway-card{ cursor:pointer; }
        .gateway-card input[type="radio"]{ width:18px; height:18px; }
        .gateway-card.active{ border-color:#0d6efd; box-shadow:0 0 0 0.15rem rgba(13,110,253,.25); }

		li.shipping{
			display: inline-flex;
			width: 100%;
			font-size: 14px;
		}
		li.shipping .input-group-icon {
			width: 100%;
			margin-left: 10px;
		}
		.input-group-icon .icon {
			position: absolute;
			left: 20px;
			top: 0;
			line-height: 40px;
			z-index: 3;
		}
		.form-select {
			height: 30px;
			width: 100%;
		}
		.form-select .nice-select {
			border: none;
			border-radius: 0px;
			height: 40px;
			background: #f6f6f6 !important;
			padding-left: 45px;
			padding-right: 40px;
			width: 100%;
		}
		.list li{
			margin-bottom:0 !important;
		}
		.list li:hover{
			background:#F7941D !important;
			color:white !important;
		}
		.form-select .nice-select::after {
			top: 14px;
		}
	</style>
@endpush
@push('scripts')
	<script src="{{asset('frontend/js/nice-select/js/jquery.nice-select.min.js')}}"></script>
	<script src="{{ asset('frontend/js/select2/js/select2.min.js') }}"></script>
	<script src="{{ asset('frontend/js/checkout.js') }}"></script>

@endpush