@extends('backend.layouts.master')

@section('title', trans('app.edit_review'))

@section('main-content')
<div class="card">
  <h5 class="card-header">{{ trans('app.edit_review') }}</h5>
  <div class="card-body">
    <form action="{{route('review.update',$review->id)}}" method="POST">
      @csrf
      @method('PATCH')
      <div class="form-group">
        <label for="name">{{ trans('app.customer') }}:</label>
        <input type="text" disabled class="form-control" value="{{$review->user_info->name}}">
      </div>
      <div class="form-group">
        <label for="review">{{ trans('app.comment') }}</label>
      <textarea name="review" id="" cols="20" rows="10" class="form-control">{{$review->review}}</textarea>
      </div>
      <div class="form-group">
        <label for="status">{{ trans('app.status') }} :</label>
        <select name="status" id="" class="form-control">
          <option value="">--{{ trans('app.status') }}--</option>
          <option value="active" {{(($review->status=='active')? 'selected' : '')}}>{{ trans('app.active') }}</option>
          <option value="inactive" {{(($review->status=='inactive')? 'selected' : '')}}>{{ trans('app.inactive') }}</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">{{ trans('app.update') }}</button>
    </form>
  </div>
</div>
@endsection

@push('styles')
<style>
    .order-info,.shipping-info{
        background:#ECECEC;
        padding:20px;
    }
    .order-info h4,.shipping-info h4{
        text-decoration: underline;
    }
</style>
@endpush