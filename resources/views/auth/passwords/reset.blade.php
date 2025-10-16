@extends('layouts.app')

@section('title', __('auth.reset_password'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-lock mr-2"></i>
                        {{ __('auth.reset_password') }}
                    </h1>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">{{ __('auth.reset_password_instructions') }}</p>

                    <form method="POST" action="{{ route('password.update') }}" novalidate>
                        @csrf

                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">
                                {{ __('auth.email_address') }} <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-6">
                                <input id="email" 
                                       type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ $email ?? old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus 
                                       placeholder="{{ __('auth.enter_email') }}"
                                       aria-describedby="emailHelp">

                                @error('email')
                                    <div class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                
                                <small id="emailHelp" class="form-text text-muted">
                                    {{ __('auth.email_help_text') }}
                                </small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">
                                {{ __('auth.password') }} <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-6">
                                <input id="password" 
                                       type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" 
                                       required 
                                       autocomplete="new-password"
                                       minlength="8"
                                       aria-describedby="passwordHelp">

                                @error('password')
                                    <div class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                
                                <small id="passwordHelp" class="form-text text-muted">
                                    {{ __('auth.password_requirements') }}
                                </small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">
                                {{ __('auth.confirm_password') }} <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-6">
                                <input id="password-confirm" 
                                       type="password" 
                                       class="form-control @error('password_confirmation') is-invalid @enderror" 
                                       name="password_confirmation" 
                                       required 
                                       autocomplete="new-password"
                                       minlength="8"
                                       aria-describedby="passwordConfirmHelp">

                                @error('password_confirmation')
                                    <div class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                
                                <small id="passwordConfirmHelp" class="form-text text-muted">
                                    {{ __('auth.confirm_password_help') }}
                                </small>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    {{ __('auth.reset_password') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
