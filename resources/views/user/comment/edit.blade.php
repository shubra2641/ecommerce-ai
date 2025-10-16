@extends('user.layouts.master')

@section('title', getPageTitle(__('user.edit_comment')))

@section('main-content')
<div class="card">
  <h5 class="card-header">
    <i class="fas fa-edit"></i> {{ __('user.edit_comment') }}
  </h5>
  <div class="card-body">
    <form action="{{route('user.post-comment.update',$comment->id)}}" method="POST" novalidate>
      @csrf
      @method('PATCH')
      <div class="form-group">
        <label for="name" class="form-label">
          <i class="fas fa-user"></i> {{ __('user.by') }} <span class="text-danger">*</span>
        </label>
        <input type="text" id="name" disabled class="form-control" value="{{$comment->user_info->name}}" aria-describedby="nameHelp">
        <small id="nameHelp" class="form-text text-muted">{{ __('user.comment_author') }}</small>
      </div>
      <div class="form-group">
        <label for="comment" class="form-label">
          <i class="fas fa-comment"></i> {{ __('user.comment') }} <span class="text-danger">*</span>
        </label>
        <textarea name="comment" id="comment" cols="20" rows="10" class="form-control" required minlength="10" maxlength="1000" aria-describedby="commentHelp">{{$comment->comment}}</textarea>
        <small id="commentHelp" class="form-text text-muted">{{ __('user.comment_help') }}</small>
        @error('comment')
        <div class="invalid-feedback d-block">{{$message}}</div>
        @enderror
      </div>
      <div class="form-group">
        <label for="status" class="form-label">
          <i class="fas fa-toggle-on"></i> {{ __('user.status') }} <span class="text-danger">*</span>
        </label>
        <select name="status" id="status" class="form-control" required aria-describedby="statusHelp">
          <option value="">{{ __('user.select_status') }}</option>
          <option value="active" {{(($comment->status=='active')? 'selected' : '')}}>{{ __('user.active') }}</option>
          <option value="inactive" {{(($comment->status=='inactive')? 'selected' : '')}}>{{ __('user.inactive') }}</option>
        </select>
        <small id="statusHelp" class="form-text text-muted">{{ __('user.status_help') }}</small>
        @error('status')
        <div class="invalid-feedback d-block">{{$message}}</div>
        @enderror
      </div>
      <div class="form-group">
        <button type="submit" class="btn btn-primary btn-lg">
          <i class="fas fa-save"></i> {{ __('app.update') }}
        </button>
        <a href="{{route('user.post-comment.index')}}" class="btn btn-secondary btn-lg ml-2">
          <i class="fas fa-arrow-left"></i> {{ __('app.back') }}
        </a>
      </div>
    </form>
  </div>
</div>
@endsection