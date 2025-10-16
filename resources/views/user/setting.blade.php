@extends('user.layouts.master')

@section('title', getPageTitle(__('user.settings')))

@section('main-content')

<div class="card">
    <h5 class="card-header">
        <i class="fas fa-cog"></i> {{ __('user.settings') }}
    </h5>
    <div class="card-body">
        <form method="post" action="{{route('settings.update')}}" novalidate>
            @csrf 
            <div class="form-group">
                <label for="short_des" class="form-label">
                    <i class="fas fa-align-left"></i> {{ __('user.short_description') }} <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="short_des" name="short_des" required minlength="10" maxlength="500" aria-describedby="shortDesHelp">{{$data->short_des}}</textarea>
                <small id="shortDesHelp" class="form-text text-muted">{{ __('user.short_description_help') }}</small>
                @error('short_des')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="description" class="form-label">
                    <i class="fas fa-file-text"></i> {{ __('user.description') }} <span class="text-danger">*</span>
                </label>
                <textarea class="form-control" id="description" name="description" required minlength="20" maxlength="2000" aria-describedby="descriptionHelp">{{$data->description}}</textarea>
                <small id="descriptionHelp" class="form-text text-muted">{{ __('user.description_help') }}</small>
                @error('description')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="photo" class="form-label">
                    <i class="fas fa-image"></i> {{ __('user.photo') }} <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <span class="input-group-btn">
                        <a id="lfm" data-input="thumbnail" data-preview="holder" class="btn btn-primary">
                            <i class="fas fa-image"></i> {{ __('user.choose') }}
                        </a>
                    </span>
                    <input id="thumbnail" class="form-control" type="text" name="photo" value="{{$data->photo}}" required aria-describedby="photoHelp">
                </div>
                <div id="holder" class="mt-3"></div>
                <small id="photoHelp" class="form-text text-muted">{{ __('user.photo_help') }}</small>
                @error('photo')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="address" class="form-label">
                    <i class="fas fa-map-marker-alt"></i> {{ __('user.address') }} <span class="text-danger">*</span>
                </label>
                <input type="text" class="form-control" id="address" name="address" required minlength="10" maxlength="255" value="{{$data->address}}" aria-describedby="addressHelp">
                <small id="addressHelp" class="form-text text-muted">{{ __('user.address_help') }}</small>
                @error('address')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="email" class="form-label">
                    <i class="fas fa-envelope"></i> {{ __('user.email') }} <span class="text-danger">*</span>
                </label>
                <input type="email" class="form-control" id="email" name="email" required value="{{$data->email}}" aria-describedby="emailHelp">
                <small id="emailHelp" class="form-text text-muted">{{ __('user.email_help') }}</small>
                @error('email')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="phone" class="form-label">
                    <i class="fas fa-phone"></i> {{ __('user.phone') }} <span class="text-danger">*</span>
                </label>
                <input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9+\-\s()]+" minlength="10" maxlength="20" value="{{$data->phone}}" aria-describedby="phoneHelp">
                <small id="phoneHelp" class="form-text text-muted">{{ __('user.phone_help') }}</small>
                @error('phone')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="facebook" class="form-label">
                    <i class="fab fa-facebook"></i> {{ __('user.facebook') }}
                </label>
                <input type="url" class="form-control" id="facebook" name="facebook" value="{{$data->facebook}}" aria-describedby="facebookHelp">
                <small id="facebookHelp" class="form-text text-muted">{{ __('user.facebook_help') }}</small>
                @error('facebook')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="instagram" class="form-label">
                    <i class="fab fa-instagram"></i> {{ __('user.instagram') }}
                </label>
                <input type="url" class="form-control" id="instagram" name="instagram" value="{{$data->instagram}}" aria-describedby="instagramHelp">
                <small id="instagramHelp" class="form-text text-muted">{{ __('user.instagram_help') }}</small>
                @error('instagram')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="youtube" class="form-label">
                    <i class="fab fa-youtube"></i> {{ __('user.youtube') }}
                </label>
                <input type="url" class="form-control" id="youtube" name="youtube" value="{{$data->youtube}}" aria-describedby="youtubeHelp">
                <small id="youtubeHelp" class="form-text text-muted">{{ __('user.youtube_help') }}</small>
                @error('youtube')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="twitter" class="form-label">
                    <i class="fab fa-twitter"></i> {{ __('user.twitter') }}
                </label>
                <input type="url" class="form-control" id="twitter" name="twitter" value="{{$data->twitter}}" aria-describedby="twitterHelp">
                <small id="twitterHelp" class="form-text text-muted">{{ __('user.twitter_help') }}</small>
                @error('twitter')
                <div class="invalid-feedback d-block">{{$message}}</div>
                @enderror
            </div>
            
            <div class="form-group mb-3">
                <button class="btn btn-success btn-lg" type="submit">
                    <i class="fas fa-save"></i> {{ __('app.update') }}
                </button>
                <a href="{{route('user.dashboard')}}" class="btn btn-secondary btn-lg ml-2">
                    <i class="fas fa-arrow-left"></i> {{ __('app.back') }}
                </a>
            </div>
        </form>
    </div>
</div>

@endsection