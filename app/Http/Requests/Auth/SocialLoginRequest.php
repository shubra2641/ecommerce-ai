<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SocialLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'provider' => [
                'required',
                'string',
                'in:google,facebook,github'
            ]
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'provider.required' => 'The authentication provider is required.',
            'provider.in' => 'The selected authentication provider is invalid. Allowed providers: Google, Facebook, GitHub.'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes()
    {
        return [
            'provider' => 'authentication provider'
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        $this->merge([
            'provider' => strtolower(trim($this->provider)),
        ]);
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->has('provider') && $this->provider) {
                // Check if the provider is enabled in settings
                $settings = \App\Models\Settings::first();
                
                if (!$settings) {
                    $validator->errors()->add('provider', 'Social login is not configured.');
                    return;
                }
                
                $isEnabled = false;
                $hasCredentials = false;
                
                switch ($this->provider) {
                    case 'google':
                        $isEnabled = $settings->google_login_enabled ?? false;
                        $hasCredentials = !empty($settings->google_client_id) && !empty($settings->google_client_secret);
                        break;
                    case 'facebook':
                        $isEnabled = $settings->facebook_login_enabled ?? false;
                        $hasCredentials = !empty($settings->facebook_client_id) && !empty($settings->facebook_client_secret);
                        break;
                    case 'github':
                        $isEnabled = $settings->github_login_enabled ?? false;
                        $hasCredentials = !empty($settings->github_client_id) && !empty($settings->github_client_secret);
                        break;
                }
                
                if (!$isEnabled) {
                    $validator->errors()->add('provider', ucfirst($this->provider) . ' login is not enabled.');
                } elseif (!$hasCredentials) {
                    $validator->errors()->add('provider', ucfirst($this->provider) . ' login credentials are not configured.');
                }
            }
        });
    }
}
