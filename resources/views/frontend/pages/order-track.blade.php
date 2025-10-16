@extends('frontend.layouts.master')

@section('title','E-SHOP || Order Track Page')

@section('main-content')
    <!-- Breadcrumbs -->
    <div class="breadcrumbs">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="bread-inner">
                        <ul class="bread-list">
                            <li><a href="{{route('home')}}">{{ __('frontend.menu.home') }}<i class="ti-arrow-right"></i></a></li>
                            <li class="active"><a href="javascript:void(0);">{{ __('frontend.order_track.title') }}</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- End Breadcrumbs -->
<section class="tracking_box_area section_gap py-5">
    <div class="container">
        <div class="tracking_box_inner">
            <p>{{ __('frontend.order_track.instructions') }}</p>
            <form class="row tracking_form my-4" action="{{route('product.track.order')}}" method="post" novalidate="novalidate">
              @csrf
                <div class="col-md-8 form-group">
                    <input type="text" class="form-control p-2"  name="order_number" placeholder="{{ __('frontend.order_track.order_number_placeholder') }}">
                </div>
                <div class="col-md-8 form-group">
                    <button type="submit" value="submit" class="btn submit_btn">{{ __('frontend.order_track.track_button') }}</button>
                </div>
            </form>
            @if(isset($order))
                <div class="order-tracker mt-4">
                    <h5>{{ __('frontend.order_track.tracking_for') }} <strong>{{ $order->order_number }}</strong></h5>
                    <div class="tracker-wrap mt-3">
                        <ul class="tracker">
                            @foreach($statuses as $key => $label)
                                    <li class="tracker-step {{ (!empty($reached[$key]) ? 'active' : '') }} {{ $order->status == $key ? 'current' : '' }}">
                                    <span class="step-label">{{ $label }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                    <div class="tracker-note mt-3">
                        <strong>Current status:</strong> {{ $statuses[$order->status] ?? ucfirst($order->status) }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .tracker{list-style:none;display:flex;justify-content:space-between;padding:0;margin:0}
    .tracker-step{flex:1;text-align:center;position:relative;padding:10px 5px;color:#777}
    .tracker-step:before{content:"";width:100%;height:4px;background:#e9ecef;position:absolute;left:50%;top:22px;transform:translateX(-50%);z-index:0}
    .tracker-step:first-child:before{left:50%;width:50%;transform:translateX(0)}
    .tracker-step:last-child:before{left:0;width:50%}
    .tracker-step.active{color:#fff}
    .tracker-step.active:before{background:#007bff}
    .tracker-step .step-label{position:relative;display:inline-block;padding:8px 14px;border-radius:30px;background:#f8f9fa;z-index:1}
    .tracker-step.active .step-label{background:#007bff;color:#fff}
    .tracker-step.current .step-label{box-shadow:0 0 0 4px rgba(0,123,255,0.12)}
</style>
@endpush