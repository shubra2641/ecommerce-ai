@extends('user.layouts.master')

@section('title', getPageTitle(__('user.profile')))

@section('main-content')

<div class="card shadow mb-4">
    <div class="row">
        <div class="col-md-12">
           @include('backend.layouts.notification')
        </div>
    </div>
   <div class="card-header py-3">
     <h4 class=" font-weight-bold">{{ __('user.profile') }}</h4>
     <ul class="breadcrumbs">
         <li><a href="{{route('admin')}}" class="text-muted">{{ __('user.dashboard') }}</a></li>
         <li><a href="" class="active text-primary">{{ __('user.profile_page') }}</a></li>
     </ul>
   </div>
   <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="image">
                        @if($profile->photo)
                        <img class="card-img-top img-fluid rounded-circle mt-4 mx-auto d-block" src="{{$profile->photo}}" alt="profile picture">
                        @else 
                        <img class="card-img-top img-fluid rounded-circle mt-4 mx-auto d-block" src="{{asset('backend/img/avatar.png')}}" alt="profile picture">
                        @endif
                    </div>
                    <div class="card-body mt-4 ml-2">
                      <h5 class="card-title text-left"><small><i class="fas fa-user"></i> {{$profile->name}}</small></h5>
                      <p class="card-text text-left"><small><i class="fas fa-envelope"></i> {{$profile->email}}</small></p>
                      <p class="card-text text-left"><small class="text-muted"><i class="fas fa-hammer"></i> {{$profile->role}}</small></p>
                    </div>
                  </div>
            </div>
            <div class="col-md-8">
                <form class="border px-4 pt-2 pb-3" method="POST" action="{{route('user-profile-update',$profile->id)}}">
                    @csrf
                    <div class="form-group">
                        <label for="inputTitle" class="col-form-label">{{ __('user.table.name') }}</label>
                      <input id="inputTitle" type="text" name="name" placeholder="{{ __('user.form.enter_name') }}"  value="{{$profile->name}}" class="form-control">
                      @error('name')
                      <span class="text-danger">{{$message}}</span>
                      @enderror
                      </div>
              
                      <div class="form-group">
                          <label for="inputEmail" class="col-form-label">{{ __('user.table.email') }}</label>
                        <input id="inputEmail" disabled type="email" name="email" placeholder="{{ __('user.form.enter_email') }}"  value="{{$profile->email}}" class="form-control">
                        @error('email')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                      </div>
              
                      <div class="form-group">
                      <label for="inputPhoto" class="col-form-label">{{ __('user.photo') }}</label>
                      <div class="input-group">
                          <span class="input-group-btn">
                              <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                              <i class="fa fa-picture-o"></i> {{ __('user.choose') }}
                              </a>
                          </span>
                          <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$profile->photo}}">
                      </div>
                        @error('photo')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                      </div>
                      <div class="form-group">
                          <label for="role" class="col-form-label">{{ __('user.role') }}</label>
                          <select name="role" class="form-control">
                              <option value="">{{ __('user.select_role') }}</option>
                                  <option value="admin" {{(($profile->role=='admin')? 'selected' : '')}}>{{ __('user.admin') }}</option>
                                  <option value="user" {{(($profile->role=='user')? 'selected' : '')}}>{{ __('user.user') }}</option>
                          </select>
                        @error('role')
                        <span class="text-danger">{{$message}}</span>
                        @enderror
                        </div>

                        <button type="submit" class="btn btn-success btn-sm">{{ trans('app.update') }}</button>
                </form>
            </div>
        </div>
   </div>
</div>

@endsection

