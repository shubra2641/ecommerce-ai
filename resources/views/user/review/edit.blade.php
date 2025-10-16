@extends('user.layouts.master')

@section('title', getPageTitle(__('user.edit_review')))

@section('main-content')
<div class="card">
  <h5 class="card-header">{{ __('user.edit_review') }}</h5>
  <div class="card-body">
    <form action="{{route('user.productreview.update',$review->id)}}" method="POST">
      @csrf
      @method('PATCH')
      <div class="form-group">
        <label for="name">{{ __('user.review_by') }}:</label>
        <input type="text" disabled class="form-control" value="{{$review->user_info->name}}">
      </div>
      <div class="form-group">
        <label for="review">{{ __('user.review') }}</label>
      <textarea name="review" id="" cols="20" rows="10" class="form-control">{{$review->review}}</textarea>
      </div>
      <div class="form-group">
        <label for="status">{{ __('user.table.status') }}:</label>
        <select name="status" id="" class="form-control">
          <option value="">{{ __('user.select_status') }}</option>
          <option value="active" {{(($review->status=='active')? 'selected' : '')}}>{{ __('user.active') }}</option>
          <option value="inactive" {{(($review->status=='inactive')? 'selected' : '')}}>{{ __('user.inactive') }}</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">{{ trans('app.update') }}</button>
    </form>
  </div>
</div>
@endsection