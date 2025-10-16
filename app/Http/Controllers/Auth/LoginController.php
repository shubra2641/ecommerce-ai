<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Socialite;
use App\Models\User;
use Auth;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\SocialLoginRequest;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */

    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function credentials(Request $request)
    {
        return [
            'email' => $request->email,
            'password' => $request->password,
            'status' => 'active'
        ];
    }
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * The user has been authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role === 'user') {
            return redirect()->route('user');
        }
        
        return redirect($this->redirectTo);
    }

    /**
     * Redirect to the provider's authentication page.
     *
     * @param  \App\Http\Requests\Auth\SocialLoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect(SocialLoginRequest $request)
    {
        $provider = $request->validated()['provider'];
        
        // Get settings and update config
        $settings = \App\Models\Settings::first();
        $this->updateSocialConfig($provider, $settings);
        
        return Socialite::driver($provider)->redirect();
    }
    
    /**
     * Update social configuration with database values.
     *
     * @param  string  $provider
     * @param  \App\Models\Settings  $settings
     * @return void
     */
    private function updateSocialConfig($provider, $settings)
    {
        switch ($provider) {
            case 'google':
                config(['services.google.client_id' => $settings->google_client_id]);
                config(['services.google.client_secret' => $settings->google_client_secret]);
                break;
            case 'facebook':
                config(['services.facebook.client_id' => $settings->facebook_client_id]);
                config(['services.facebook.client_secret' => $settings->facebook_client_secret]);
                break;
            case 'github':
                config(['services.github.client_id' => $settings->github_client_id]);
                config(['services.github.client_secret' => $settings->github_client_secret]);
                break;
        }
    }
 
    /**
     * Handle callback from social provider.
     *
     * @param  \App\Http\Requests\Auth\SocialLoginRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function Callback(SocialLoginRequest $request)
    {
        $provider = $request->validated()['provider'];

        try {
            // Update config with database values
            $settings = \App\Models\Settings::first();
            if ($settings) {
                $this->updateSocialConfig($provider, $settings);
            }
            
            $userSocial = Socialite::driver($provider)->stateless()->user();
            
            // Validate social user data
            if (!$userSocial || !$userSocial->getEmail()) {
                return redirect()->route('login.form')->with('error', 'Failed to retrieve user information from ' . $provider . '.');
            }
            
            $users = User::where(['email' => $userSocial->getEmail()])->first();
            
            if ($users) {
                // Check if user is active
                if ($users->status !== 'active') {
                    return redirect()->route('login.form')->with('error', 'Your account is not active. Please contact support.');
                }
                
                Auth::login($users);
                return redirect('/')->with('success', 'You are logged in from ' . $provider);
            } else {
                // Create new user with validation
                $userData = [
                    'name' => $userSocial->getName() ?: 'User',
                    'email' => $userSocial->getEmail(),
                    'image' => $userSocial->getAvatar(),
                    'provider_id' => $userSocial->getId(),
                    'provider' => $provider,
                    'status' => 'active',
                    'role' => 'user'
                ];
                
                $user = User::create($userData);
                Auth::login($user);
                return redirect()->route('home')->with('success', 'Account created and logged in successfully.');
            }
        } catch (\Exception $e) {
            return redirect()->route('login.form')->with('error', 'Authentication failed. Please try again.');
        }
    }
}
