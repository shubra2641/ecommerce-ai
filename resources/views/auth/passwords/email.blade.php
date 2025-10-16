<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('auth.forgot_password') }} - {{ config('app.name', 'Laravel') }}</title>
    @include('backend.layouts.head')
</head>

<body class="bg-gradient-primary">

    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-2">
                                            <i class="fas fa-key mr-2"></i>
                                            {{ __('auth.forgot_password') }}
                                        </h1>
                                        <p class="mb-4 text-muted">{{ __('auth.forgot_password_instructions') }}</p>
                                    </div>
                                    
                                    @if (session('status'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            <i class="fas fa-check-circle mr-2"></i>
                                            {{ session('status') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif
                                    
                                    <form class="user" method="POST" action="{{ route('password.email') }}" novalidate>
                                        @csrf
                                        <div class="form-group">
                                            <label for="email" class="sr-only">{{ __('auth.email_address') }}</label>
                                            <input type="email" 
                                                   class="form-control form-control-user @error('email') is-invalid @enderror" 
                                                   id="email" 
                                                   name="email" 
                                                   value="{{ old('email') }}" 
                                                   placeholder="{{ __('auth.enter_email') }}" 
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
                                        
                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-paper-plane mr-2"></i>
                                            {{ __('auth.send_password_reset_link') }}
                                        </button>
                                    </form>
                                    
                                    <hr>
                                    
                                    <div class="text-center">
                                        <a class="small" href="{{ route('login') }}">
                                            <i class="fas fa-arrow-left mr-1"></i>
                                            {{ __('auth.already_have_account') }}
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>

</html>
