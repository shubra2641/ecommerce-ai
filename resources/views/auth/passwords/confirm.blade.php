@extends('layouts.app')

@section('title', __('auth.confirm_password'))

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h1 class="h4 mb-0">{{ __('auth.confirm_password') }}</h1>
                </div>

                <div class="card-body">
                    <p class="text-muted mb-4">{{ __('auth.please_confirm_password') }}</p>

                    <form method="POST" action="{{ route('password.confirm') }}" novalidate>
                        @csrf

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">
                                {{ __('Password') }} <span class="text-danger">*</span>
                            </label>

                            <div class="col-md-6">
                                <input id="password" 
                                       type="password" 
                                       class="form-control @error('password') is-invalid @enderror" 
                                       name="password" 
                                       required 
                                       autocomplete="current-password"
                                       aria-describedby="passwordHelp"
                                       minlength="6">

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

                        <div class="form-group row mb-0">
                            <div class="col-md-8 offset-md-4">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    {{ __('auth.confirm_password') }}
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
