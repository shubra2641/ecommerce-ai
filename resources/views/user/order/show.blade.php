@extends('user.layouts.master')

@section('title', getPageTitle(__('user.order_detail')))

@section('main-content')
<div class="card">
<h5 class="card-header">{{ __('user.order.title') }}       <a href="{{route('order.pdf',$order->id)}}" class=" btn btn-sm btn-primary shadow-sm float-right"><i class="fas fa-download fa-sm text-white-50"></i> {{ __('user.order.generate_pdf') }}</a>
  </h5>
  <div class="card-body">
    @if($order)
    <table class="table table-striped table-hover">
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
      <tbody>
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
                <form method="POST" action="{{route('order.destroy',[$order->id])}}">
                  @csrf
                  @method('delete')
                      <button class="btn btn-danger btn-sm dltBtn" data-id={{$order->id}} style="height:30px; width:30px;border-radius:50%" data-toggle="tooltip" data-placement="bottom" title="Delete"><i class="fas fa-trash-alt"></i></button>
                </form>
            </td>

        </tr>
      </tbody>
    </table>

    <section class="confirmation_part section_padding">
      <div class="order_boxes">
        <div class="row">
          <div class="col-lg-6 col-lx-4">
            <div class="order-info">
        <h4 class="text-center pb-4">{{ __('user.order.information') }}</h4>
              <table class="table">
                    <tr class="">
            <td>{{ __('user.order.order_number') }}</td>
            <td> : {{$order->order_number}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.order.order_date') }}</td>
            <td> : {{$order->created_at->format('D d M, Y')}} at {{$order->created_at->format('g : i a')}} </td>
                    </tr>
                    <tr>
            <td>{{ __('user.table.quantity') }}</td>
            <td> : {{$order->quantity}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.table.status') }}</td>
            <td> : {{$order->status}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.table.charge') }}</td>
            <td> :${{$order->shipping ? number_format($order->shipping->price, 2) : '0.00'}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.table.total_amount') }}</td>
            <td> : $ {{number_format($order->total_amount,2)}}</td>
                    </tr>
                    <tr>
                      <td>Payment Method</td>
                      <td> : @if($order->payment_method=='cod') Cash on Delivery @else Paypal @endif</td>
                    </tr>
                    <tr>
                        <td>Payment Status</td>
                        <td> : {{$order->payment_status}}</td>
                    </tr>
              </table>
            </div>
          </div>

          <div class="col-lg-6 col-lx-4">
            <div class="shipping-info">
        <h4 class="text-center pb-4">{{ __('user.order.shipping_information') }}</h4>
              <table class="table">
                    <tr class="">
            <td>{{ __('user.order.full_name') }}</td>
            <td> : {{$order->first_name}} {{$order->last_name}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.form.email') }}</td>
            <td> : {{$order->email}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.order.phone') }}</td>
            <td> : {{$order->phone}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.order.address') }}</td>
            <td> : {{$order->address1}}, {{$order->address2}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.order.country') }}</td>
            <td> : {{$order->country}}</td>
                    </tr>
                    <tr>
            <td>{{ __('user.order.post_code') }}</td>
            <td> : {{$order->post_code}}</td>
                    </tr>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
    @endif

  </div>
</div>
@endsection
