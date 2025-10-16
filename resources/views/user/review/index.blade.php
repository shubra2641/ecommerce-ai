@extends('user.layouts.master')

@section('title', getPageTitle(__('user.reviews')))

@section('main-content')
 <!-- DataTales Example -->
 <div class="card shadow mb-4">
     <div class="row">
         <div class="col-md-12">
            @include('backend.layouts.notification')
         </div>
     </div>
    <div class="card-header py-3">
  <h6 class="m-0 font-weight-bold text-primary float-left">{{ __('user.reviews') }}</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        @if(count($reviews)>0)
        <table class="table table-bordered" id="order-dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>{{ __('user.table.sn') }}</th>
              <th>{{ __('user.review_by') }}</th>
              <th>{{ __('user.product_title') }}</th>
              <th>{{ __('user.review') }}</th>
              <th>{{ __('user.rate') }}</th>
              <th>{{ __('user.date') }}</th>
              <th>{{ __('user.table.status') }}</th>
              <th>{{ __('user.table.action') }}</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>{{ __('user.table.sn') }}</th>
              <th>{{ __('user.review_by') }}</th>
              <th>{{ __('user.product_title') }}</th>
              <th>{{ __('user.review') }}</th>
              <th>{{ __('user.rate') }}</th>
              <th>{{ __('user.date') }}</th>
              <th>{{ __('user.table.status') }}</th>
              <th>{{ __('user.table.action') }}</th>
              </tr>
          </tfoot>
          <tbody>
            @foreach($reviews as $review)
                <tr>
                    <td>{{$review->id}}</td>
                    <td>{{$review->user_info['name']}}</td>
                    <td>{{$review->product->title}}</td>
                    <td>{{$review->review}}</td>
                    <td>
                     <ul class="d-flex rating-stars">
                          @for($i=1; $i<=5;$i++)
                          @if($review->rate >=$i)
                            <li class="star-filled"><i class="fa fa-star"></i></li>
                          @else
                            <li class="star-empty"><i class="far fa-star"></i></li>
                          @endif
                        @endfor
                     </ul>
                    </td>
                    <td>{{$review->created_at->format('M d D, Y g: i a')}}</td>
                    <td>
                        @if($review->status=='active')
                          <span class="badge badge-success">{{$review->status}}</span>
                        @else
                          <span class="badge badge-warning">{{$review->status}}</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{route('user.productreview.edit',$review->id)}}" class="btn btn-primary btn-sm float-left mr-1 action-btn" data-toggle="tooltip" title="{{ __('user.edit') }}" data-placement="bottom"><i class="fas fa-edit"></i></a>
                        <form method="POST" action="{{route('user.productreview.delete',[$review->id])}}">
                          @csrf
                          @method('delete')
                              <button class="btn btn-danger btn-sm dltBtn action-btn" data-id={{$review->id}} data-toggle="tooltip" data-placement="bottom" title="{{ __('user.delete') }}"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    </td>
                </tr>
            @endforeach
          </tbody>
        </table>
        <div class="d-flex justify-content-end">{{$reviews->links()}}</div>
        @else
          <h6 class="text-center">{{ __('user.no_reviews') }}</h6>
        @endif
      </div>
    </div>
</div>
@endsection