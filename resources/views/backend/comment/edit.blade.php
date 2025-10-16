@extends('backend.layouts.master')

@section('title', trans('app.edit_comment'))

@section('main-content')
<div class="card">
  <h5 class="card-header">{{ trans('app.edit_comment') }}</h5>
  <div class="card-body">
    <form action="{{route('comment.update',$comment->id)}}" method="POST">
      @csrf
      @method('PATCH')
      <div class="form-group">
        <label for="name">{{ trans('app.by') }}:</label>
        <input type="text" disabled class="form-control" value="{{$comment->user_info->name}}">
      </div>
      <div class="form-group">
        <label for="comment">{{ trans('app.comment') }}</label>
      <textarea name="comment" id="" cols="20" rows="10" class="form-control">{{$comment->comment}}</textarea>
      </div>
      <div class="form-group">
        <label for="status">{{ trans('app.status') }} :</label>
        <select name="status" id="" class="form-control">
          <option value="">{{ trans('app.select_status') }}</option>
          <option value="active" {{(($comment->status=='active')? 'selected' : '')}}>{{ trans('app.active') }}</option>
          <option value="inactive" {{(($comment->status=='inactive')? 'selected' : '')}}>{{ trans('app.inactive') }}</option>
        </select>
      </div>
      <button type="submit" class="btn btn-primary">{{ trans('app.update') }}</button>
    </form>
  </div>
</div>
@endsection