<!DOCTYPE html>
<html lang="en">

<head>
  <title>{{ __('auth.title_login') }} - {{ config('app.name', 'Laravel') }}</title>
  @include('backend.layouts.head')

</head>

<body class="bg-gradient-primary">

  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9 mt-5">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">
                      <i class="fas fa-sign-in-alt mr-2"></i>
                      {{ __('auth.welcome_back') }}
                    </h1>
                  </div>
                  
                  <form class="user" method="POST" action="{{ route('login') }}" novalidate>
                    @csrf
                    
                    <div class="form-group">
                      <label for="email" class="sr-only">{{ __('auth.email_address') }}</label>
                      <input type="email" 
                             class="form-control form-control-user @error('email') is-invalid @enderror" 
                             name="email" 
                             value="{{ old('email') }}" 
                             id="email" 
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
                    
                    <div class="form-group">
                      <label for="password" class="sr-only">{{ __('auth.password') }}</label>
                      <input type="password" 
                             class="form-control form-control-user @error('password') is-invalid @enderror" 
                             id="password" 
                             placeholder="{{ __('auth.password') }}" 
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
                    
                    <div class="form-group">
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
                    
                    <button type="submit" class="btn btn-primary btn-user btn-block">
                      <i class="fas fa-sign-in-alt mr-2"></i>
                      {{ __('auth.login') }}
                    </button>
                  </form>
                  <hr>
                   
                  <div class="text-center">
                    @if (Route::has('password.request'))
            <a class="btn btn-link small" href="{{ route('password.request') }}">
              {{ __('auth.forgot_password_question') }}
            </a>
                    @endif
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
