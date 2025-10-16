@extends('user.layouts.master')

@section('title', getPageTitle(__('user.orders')))

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('user.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
  <h6 class="m-0 font-weight-bold text-primary float-left">{{ __('user.order.title') }} Lists</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($orders)>0)
        <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>{{ __('user.table.sn') }}</th>
              <th>{{ __('user.table.order_no') }}</th>
              <th>{{ __('user.table.name') }}</th>
              <th>{{ __('user.table.email') }}</th>
              <th>{{ __('user.table.quantity') }}</th>
              <th>{{ __('user.table.charge') }}</th>
              <th>{{ __('user.table.total_amount') }}</th>
              <th>{{ __('user.table.status') }}</th>
              <th>{{ __('user.table.action') }}</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>S.N.</th>
              <th>Order No.</th>
              <th>Name</th>
              <th>Email</th>
              <th>Quantity</th>
              <th>Charge</th>
              <th>Total Amount</th>
              <th>Status</th>
              <th>Action</th>
              </tr>
          </tfoot>
          <tbody>
            @foreach($orders as $order)
                <tr>
                    <td>{{$order->id}}</td>
                    <td>{{$order->order_number}}</td>
                    <td>{{$order->first_name}} {{$order->last_name}}</td>
                    <td>{{$order->email}}</td>
                    <td>{{$order->quantity}}</td>
                    <td>${{$order->shipping ? number_format($order->shipping->price, 2) : '0.00'}}</td>
                    <td>${{number_format($order->total_amount,2)}}</td>
                    <td>
                        @if($order->status=='new')
                          <span class="badge badge-primary">{{$order->status}}</span>
                        @elseif($order->status=='process')
                          <span class="badge badge-warning">{{$order->status}}</span>
                        @elseif($order->status=='delivered')
                          <span class="badge badge-success">{{$order->status}}</span>
                        @else
                          <span class="badge badge-danger">{{$order->status}}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{route('user.order.show',$order->id)}}" class="btn btn-warning btn-sm float-left mr-1 action-btn" data-toggle="tooltip" title="{{ __('user.view') }}" data-placement="bottom"><i class="fas fa-eye"></i></a>
                        <form method="POST" action="{{route('user.order.delete',[$order->id])}}">
                          @csrf
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn action-btn" data-id={{$order->id}} data-toggle="tooltip" data-placement="bottom" title="{{ __('user.delete') }}"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        <div class="d-flex justify-content-end">{{$orders->links()}}</div>
        @else
          <h6 class="text-center">{{ __('user.no_orders_found') }}</h6>
        @endif
      </div>
    </div>
</div>
@endsection

{{-- Styles and scripts moved to layout files --}}
