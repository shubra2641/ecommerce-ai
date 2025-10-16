@extends('frontend.layouts.master')
@section('title','Compare Products')
@section('main-content')
<div class="breadcrumbs">
  <div class="container"><div class="row"><div class="col-12"><div class="bread-inner"><ul class="bread-list"><li><a href='{{route('home')}}'>Home<i class="ti-arrow-right"></i></a></li><li class='active'><a href="javascript:void(0);">Compare</a></li></ul></div></div></div></div>
</div>
<section class="section">
 <div class="container">
  <div class="row">
   <div class="col-12">
    @if(session('success'))<div class="alert alert-success">{{session('success')}}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{session('error')}}</div>@endif
    <div class="table-responsive">
     <table class="table table-bordered table-striped">
      <thead>
       <tr>
        <th>Feature</th>
        @foreach($compares as $c)
          <th>
            <div class="text-center">
              <img src="{{$c->product->first_photo ?? ''}}" style="max-width:120px" alt="{{$c->product->title}}"/>
              <p class="mt-2">{{$c->product->title}}</p>
              <a class="btn btn-sm btn-danger" href="{{route('compare.remove',$c->id)}}">Remove</a>
            </div>
          </th>
        @endforeach
       </tr>
      </thead>
      <tbody>
       <tr>
        <td>Price</td>
        @foreach($compares as $c)
          <td>${{number_format($c->price,2)}}</td>
        @endforeach
       </tr>
       <tr>
        <td>Discounted</td>
        @foreach($compares as $c)
          <td>${{number_format($c->compare_at_price,2)}}</td>
        @endforeach
       </tr>
       <tr>
        <td>SKU</td>
        @foreach($compares as $c)
          <td>{{$c->product->sku ?? '-'}}</td>
        @endforeach
       </tr>
       <tr>
        <td>Category</td>
        @foreach($compares as $c)
          <td>{{$c->product->cat_info->title ?? '-'}}</td>
        @endforeach
       </tr>
       <tr>
        <td>Brand</td>
        @foreach($compares as $c)
          <td>{{$c->product->brand->title ?? '-'}}</td>
        @endforeach
       </tr>
       <tr>
        <td>Status</td>
        @foreach($compares as $c)
          <td>{{$c->product->status}}</td>
        @endforeach
       </tr>
       <tr>
        <td>Description</td>
        @foreach($compares as $c)
          <td style="max-width:250px">{!! safe_html(\Illuminate\Support\Str::limit($c->product->summary,150)) !!}</td>
        @endforeach
       </tr>
      </tbody>
     </table>
    </div>
    @if(count($compares))
      <a href="{{route('compare.clear')}}" class="btn btn-warning">Clear All</a>
    @endif
    <a href="{{route('product-grids')}}" class="btn btn-primary">Continue Shopping</a>
   </div>
  </div>
 </div>
</section>
@endsection
