@extends('layouts.app')

@section('title', __('auth.login'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">
                        <i class="fas fa-sign-in-alt mr-2"></i>
                        {{ __('auth.login') }}
                    </h1>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">
                                {{ __('auth.email_address') }} <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-6">
                                <input id="email" 
                                       type="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       name="email" 
                                       value="{{ old('email') }}" 
                                       required 
                                       autocomplete="email" 
                                       autofocus
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
                                       autocomplete="current-password"
                                       minlength="6"
                                       aria-describedby="passwordHelp">

                                @error('password')
                                    <div class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </div>
                                @enderror
                                
                                <small id="passwordHelp" class="form-text text-muted">
                                    {{ __('auth.password_help_text') }}
                                </small>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-md-6 offset-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="remember" 
                                           id="remember" 
                                           {{ old('remember') ? 'checked' : '' }}>

                                    <label class="form-check-label" for="remember">
                                        <i class="fas fa-check-square mr-1"></i>
                                        {{ __('auth.remember_me') }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-sign-in-alt mr-2"></i>
                                    {{ __('auth.login') }}
                                </button>

                                @if (Route::has('password.request'))
                                    <a class="btn btn-link ml-2" href="{{ route('password.request') }}">
                                        <i class="fas fa-question-circle mr-1"></i>
                                        {{ __('auth.forgot_password_question') }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
