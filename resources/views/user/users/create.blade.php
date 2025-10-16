@extends('user.layouts.master')

@section('title', getPageTitle(__('user.add_user')))

@section('main-content')

<div class="card">
  <h5 class="card-header">{{ __('user.add_user') }}</h5>
    <div class="card-body">
      <form method="post" action="{{route('users.store')}}">
        {{csrf_field()}}
        <div class="form-group">
          <label for="inputTitle" class="col-form-label">{{ __('user.form.name') }}</label>
        <input id="inputTitle" type="text" name="name" placeholder="{{ __('user.form.enter_name') }}"  value="{{old('name')}}" class="form-control">
        @error('name')
        <span class="text-danger">{{$message}}</span>
        @enderror
        </div>

        <div class="form-group">
            <label for="inputEmail" class="col-form-label">{{ __('user.form.email') }}</label>
          <input id="inputEmail" type="email" name="email" placeholder="{{ __('user.form.enter_email') }}"  value="{{old('email')}}" class="form-control">
          @error('email')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
            <label for="inputPassword" class="col-form-label">{{ __('user.form.password') }}</label>
          <input id="inputPassword" type="password" name="password" placeholder="{{ __('user.form.enter_password') }}"  value="{{old('password')}}" class="form-control">
          @error('password')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>

        <div class="form-group">
  <label for="inputPhoto" class="col-form-label">{{ __('user.form.photo') }}</label>
        <div class="input-group">
            <span class="input-group-btn">
                <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                <i class="fa fa-picture-o"></i> Choose
                </a>
            </span>
            <input id="thumbnail" class="form-control" type="text" name="photo" value="{{old('photo')}}">
        </div>
  <img id="holder" class="mt-3 user-photo-preview">
          @error('photo')
          <span class="text-danger">{{$message}}</span>
          @enderror
        </div>
        <div class="form-group">
            <label for="role" class="col-form-label">{{ __('user.role') }}</label>
            <select name="role" class="form-control">
                <option value="">{{ __('user.select_role') }}</option>
                @foreach($roles as $role)
                    <option value="{{$role->role}}">{{ ucfirst($role->role) }}</option>
                @endforeach
            </select>
          @error('role')
          <span class="text-danger">{{$message}}</span>
          @enderror
          </div>
          <div class="form-group">
            <label for="status" class="col-form-label">{{ __('user.table.status') }}</label>
            <select name="status" class="form-control">
                <option value="active">{{ __('user.active') }}</option>
                <option value="inactive">{{ __('user.inactive') }}</option>
            </select>
          @error('status')
          <span class="text-danger">{{$message}}</span>
          @enderror
          </div>
        <div class="form-group mb-3">
          <button type="reset" class="btn btn-warning">{{ __('user.form.reset') }}</button>
           <button class="btn btn-success" type="submit">{{ __('user.form.submit') }}</button>
        </div>
      </form>
    </div>
</div>

@endsection