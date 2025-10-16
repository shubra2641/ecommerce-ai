@extends('user.layouts.master')

@section('title', getPageTitle(__('user.dashboard')))

@section('main-content')
<div class="container-fluid">
    @include('user.layouts.notification')
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-tachometer-alt"></i> {{ __('user.dashboard') }}
        </h1>
    </div>

    <!-- Content Row -->
    <div class="row">
      <!-- Order -->
      <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-shopping-cart"></i> {{ __('user.orders') }}
                </h6>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>{{ __('user.table.sn') }}</th>
                                <th>{{ __('user.table.order_no') }}</th>
                                <th>{{ __('user.table.name') }}</th>
                                <th>{{ __('user.table.email') }}</th>
                                <th>{{ __('user.table.quantity') }}</th>
                                <th>{{ __('user.table.total_amount') }}</th>
                                <th>{{ __('user.table.status') }}</th>
                                <th>{{ __('user.table.action') }}</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th>{{ __('user.table.sn') }}</th>
                                <th>{{ __('user.table.order_no') }}</th>
                                <th>{{ __('user.table.name') }}</th>
                                <th>{{ __('user.table.email') }}</th>
                                <th>{{ __('user.table.quantity') }}</th>
                                <th>{{ __('user.table.total_amount') }}</th>
                                <th>{{ __('user.table.status') }}</th>
                                <th>{{ __('user.table.action') }}</th>
                            </tr>
                        </tfoot>
                        <tbody>
                            @if(count($orders)>0)
                                @foreach($orders as $order)   
                                    <tr>
                                        <td>{{$order->id}}</td>
                                        <td>{{$order->order_number}}</td>
                                        <td>{{$order->first_name}} {{$order->last_name}}</td>
                                        <td>{{$order->email}}</td>
                                        <td>{{$order->quantity}}</td>
                                        <td>${{number_format($order->total_amount,2)}}</td>
                                        <td>
                                            @if($order->status=='new')
                                                <span class="badge badge-primary">
                                                    <i class="fas fa-clock"></i> {{$order->status}}
                                                </span>
                                            @elseif($order->status=='process')
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-cog"></i> {{$order->status}}
                                                </span>
                                            @elseif($order->status=='delivered')
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check-circle"></i> {{$order->status}}
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times-circle"></i> {{$order->status}}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group" aria-label="{{ __('user.actions') }}">
                                                <a href="{{route('user.order.show',$order->id)}}" 
                                                   class="btn btn-warning btn-sm" 
                                                   data-toggle="tooltip" 
                                                   title="{{ __('user.view') }}" 
                                                   data-placement="bottom">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <form method="POST" action="{{route('user.order.delete',[$order->id])}}" class="d-inline">
                                                    @csrf 
                                                    @method('delete')
                                                    <button class="btn btn-danger btn-sm dltBtn" 
                                                            data-id="{{$order->id}}" 
                                                            data-toggle="tooltip" 
                                                            data-placement="bottom" 
                                                            title="{{ __('user.delete') }}">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>  
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="8" class="text-center">
                                        <div class="py-5">
                                            <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                            <h4 class="text-muted">{{ __('user.no_orders') }}</h4>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end">
                    {{$orders->links()}}
                </div>
            </div>
        </div>
      </div>
    </div>

  </div>
@endsection