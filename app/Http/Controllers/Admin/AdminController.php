<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use App\Http\Controllers\Controller;
use App\Models\Settings;
use App\Models\User;
use App\Rules\MatchOldPassword;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;
use Exception;
use App\Http\Requests\Admin\AdminProfileUpdateRequest;
use App\Http\Requests\Admin\AdminSettingsUpdateRequest;
use App\Http\Requests\Admin\AdminChangePasswordRequest;

/**
 * AdminController handles all administrative operations
 * 
 * This controller manages admin dashboard, user statistics, profile management,
 * settings configuration, password changes, and system maintenance tasks.
 */
class AdminController extends Controller
{
    /**
     * Display admin dashboard with user statistics
     * 
     * Shows a chart of user registrations over the last 7 days
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $userStats = $this->getUserRegistrationStats();
            return view('backend.index')->with('users', json_encode($userStats));
        } catch (Exception $e) {
            // Log error and return empty chart data
            \Log::error('Error fetching user statistics: ' . $e->getMessage());
            $array = [['Name', 'Number']];
            return view('backend.index')->with('users', json_encode($array));
        }
    }

    /**
     * Display admin profile page
     * 
     * @return View
     */
    public function profile(): View
    {
        try {
            $profile = auth()->user();
            return view('backend.users.profile')->with('profile', $profile);
        } catch (Exception $e) {
            \Log::error('Error fetching admin profile: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load profile data');
            return redirect()->route('admin');
        }
    }

    /**
     * Update admin profile information
     * 
     * Validates and updates the authenticated admin's profile information.
     * Includes security checks to ensure users can only update their own profiles
     * and prevents email conflicts with existing users.
     * 
     * @param AdminProfileUpdateRequest $request The validated form request containing profile data
     * @param int $id The user ID to update (must match authenticated user)
     * @return RedirectResponse Redirects back with success/error message
     */
    public function profileUpdate(AdminProfileUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            // Use validated data from the FormRequest
            $validatedData = $request->validated();

            // Ensure the user can only update their own profile
            if (auth()->id() !== $id) {
                request()->session()->flash('error', 'Unauthorized access to profile update');
                return redirect()->back();
            }

            $user = User::findOrFail($id);
            
            // Only update allowed fields to prevent mass assignment
            $allowedFields = ['name', 'email', 'phone', 'address'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            
            // Check if email is being changed and if it's already taken
            if (isset($data['email']) && $data['email'] !== $user->email) {
                $existingUser = User::where('email', $data['email'])->where('id', '!=', $id)->first();
                if ($existingUser) {
                    request()->session()->flash('error', 'Email address is already taken');
                    return redirect()->back();
                }
            }
            
            $status = $user->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Successfully updated your profile');
            } else {
                request()->session()->flash('error', 'Please try again!');
            }
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('User not found for profile update: ' . $e->getMessage());
            request()->session()->flash('error', 'User not found');
        } catch (Exception $e) {
            \Log::error('Error updating admin profile: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while updating your profile');
        }
        
        return redirect()->back();
    }

    /**
     * Display system settings page
     * 
     * @return View
     */
    public function settings(): View
    {
        try {
            $data = Settings::first();
            return view('backend.setting')->with('data', $data);
        } catch (Exception $e) {
            \Log::error('Error fetching settings: ' . $e->getMessage());
            request()->session()->flash('error', 'Unable to load settings data');
            return redirect()->route('admin');
        }
    }

    /**
     * Update system settings
     * 
     * Updates various system configuration settings including AI provider settings,
     * social login configurations, and frontend/backend customization options.
     * Handles checkbox fields and prevents mass assignment vulnerabilities.
     * 
     * @param AdminSettingsUpdateRequest $request The validated form request containing settings data
     * @return RedirectResponse Redirects to admin dashboard with success/error message
     */
    public function settingsUpdate(AdminSettingsUpdateRequest $request): RedirectResponse
    {
        try {
            // Use validated data from AdminSettingsUpdateRequest
            $validatedData = $request->validated();

            $settings = Settings::first();
            if (!$settings) {
                request()->session()->flash('error', 'Settings not found');
                return redirect()->route('admin');
            }

            // Get only allowed fields to prevent mass assignment
            $allowedFields = [
                // short_des and description handled separately for multilingual support
                'photo', 'logo', 'address', 'email', 'phone',
                'ai_provider', 'ai_api_key', 'ai_model', 'ai_max_tokens', 'ai_temperature',
                'azure_endpoint', 'azure_deployment', 'google_client_id', 'google_client_secret',
                'facebook_client_id', 'facebook_client_secret', 'github_client_id', 'github_client_secret',
                'frontend_font_family', 'frontend_font_size', 'frontend_font_weight',
                'backend_font_family', 'backend_font_size', 'backend_font_weight',
                'google_fonts_url'
            ];
            
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            // Handle multilingual short_des and description if provided
            if (isset($validatedData['short_des']) && is_array($validatedData['short_des'])) {
                $translations = $settings->translations ?? [];
                foreach ($validatedData['short_des'] as $code => $value) {
                    $translations[$code] = array_merge($translations[$code] ?? [], ['short_des' => $value]);
                }
                $data['translations'] = $translations;
                // fallback short_des
                $default = app()->getLocale();
                $data['short_des'] = $validatedData['short_des'][$default] ?? ($settings->short_des ?? null);
            }

            if (isset($validatedData['description']) && is_array($validatedData['description'])) {
                $translations = $data['translations'] ?? $settings->translations ?? [];
                foreach ($validatedData['description'] as $code => $value) {
                    $translations[$code] = array_merge($translations[$code] ?? [], ['description' => $value]);
                }
                $data['translations'] = $translations;
                // fallback description
                $default = app()->getLocale();
                $data['description'] = $validatedData['description'][$default] ?? ($settings->description ?? null);
            }

            // Handle multilingual site_name if provided
            if (isset($validatedData['site_name']) && is_array($validatedData['site_name'])) {
                $translations = $settings->translations ?? [];
                foreach ($validatedData['site_name'] as $code => $value) {
                    $translations[$code] = array_merge($translations[$code] ?? [], ['site_name' => $value]);
                }
                $data['translations'] = $translations;
                // set a fallback site_name (default locale) if empty
                $default = app()->getLocale();
                $data['site_name'] = $validatedData['site_name'][$default] ?? ($settings->site_name ?? null);
            }
            
            // Normalize checkbox fields
            $data['ai_enabled'] = $request->has('ai_enabled') ? 1 : 0;
            $data['google_login_enabled'] = $request->has('google_login_enabled') ? 1 : 0;
            $data['facebook_login_enabled'] = $request->has('facebook_login_enabled') ? 1 : 0;
            $data['github_login_enabled'] = $request->has('github_login_enabled') ? 1 : 0;
            $data['use_google_fonts'] = $request->has('use_google_fonts') ? 1 : 0;

            try {
                $status = $settings->update($data);
                if ($status) {
                    request()->session()->flash('success', 'Settings successfully updated');
                } else {
                    request()->session()->flash('error', 'Please try again');
                }
            } catch (\Illuminate\Database\QueryException $qe) {
                // DB level error (missing column, invalid JSON, etc.)
                \Log::error('DB error updating settings: ' . $qe->getMessage(), [
                    'sql' => $qe->getSql(),
                    'bindings' => $qe->getBindings(),
                ]);
                request()->session()->flash('error', 'Database error while updating settings: ' . $qe->getMessage());
                return redirect()->back();
            }
        } catch (Exception $e) {
            // Log full exception for debugging
            \Log::error('Error updating settings: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            // Show the message in the session (helps admin debug). If you prefer not to show raw messages, change this.
            request()->session()->flash('error', 'An error occurred while updating settings: ' . $e->getMessage());
        }
        
        return redirect()->route('admin');
    }

    /**
     * Display change password form
     * 
     * @return View
     */
    public function changePassword(): View
    {
        return view('backend.layouts.changePassword');
    }

    /**
     * Store new password after validation
     * 
     * Validates the current password and updates it with a new hashed password.
     * Includes security verification to ensure the current password is correct
     * before allowing the password change.
     * 
     * @param AdminChangePasswordRequest $request The validated form request containing password data
     * @return RedirectResponse Redirects to admin dashboard or back with success/error message
     */
    public function changPasswordStore(AdminChangePasswordRequest $request): RedirectResponse
    {
        try {
            // Use validated data from the FormRequest
            $validatedData = $request->validated();

            $user = User::find(auth()->user()->id);
            if (!$user) {
                request()->session()->flash('error', 'User not found');
                return redirect()->back();
            }

            // Verify current password before updating
            if (!Hash::check($validatedData['current_password'], $user->password)) {
                request()->session()->flash('error', 'Current password is incorrect');
                return redirect()->back();
            }

            // Update password with hashed new password
            $user->update(['password' => Hash::make($validatedData['new_password'])]);
            
            request()->session()->flash('success', 'Password successfully changed');
            return redirect()->route('admin');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            \Log::error('User not found for password change: ' . $e->getMessage());
            request()->session()->flash('error', 'User not found');
        } catch (Exception $e) {
            \Log::error('Error changing password: ' . $e->getMessage());
            request()->session()->flash('error', 'An error occurred while changing password');
        }
        
        return redirect()->back();
    }

    /**
     * Get user statistics for pie chart display
     * 
     * This method reuses the same logic as index() to avoid code duplication
     * 
     * @param Request $request
     * @return View
     */
    public function userPieChart(Request $request): View
    {
        try {
            $userStats = $this->getUserRegistrationStats();
            return view('backend.index')->with('course', json_encode($userStats));
        } catch (Exception $e) {
            \Log::error('Error fetching user pie chart data: ' . $e->getMessage());
            $array = [['Name', 'Number']];
            return view('backend.index')->with('course', json_encode($array));
        }
    }

    /**
     * Create or recreate storage symbolic link
     * 
     * This method creates a symbolic link from public/storage to storage/app/public
     * to make uploaded files accessible via web. If the link already exists,
     * it removes the old one and creates a new one to ensure proper functionality.
     * 
     * @return RedirectResponse Redirects back with success/error message
     */
    public function storageLink(): RedirectResponse
    {
        try {
            $storagePath = public_path('storage');
            
            // Check if the storage folder already exists
            if (File::exists($storagePath)) {
                // Remove the existing symbolic link
                File::delete($storagePath);
            }

            // Create new storage link
            Artisan::call('storage:link');
            request()->session()->flash('success', 'Storage link created successfully.');
            
        } catch (Exception $exception) {
            \Log::error('Error creating storage link: ' . $exception->getMessage());
            request()->session()->flash('error', 'Failed to create storage link: ' . $exception->getMessage());
        }
        
        return redirect()->back();
    }

    /**
     * Get user registration statistics for the last 7 days
     * 
     * This private method centralizes the logic for fetching user statistics
     * to avoid code duplication between index() and userPieChart() methods
     * 
     * @return array
     */
    private function getUserRegistrationStats(): array
    {
        // Get user registration data for the last 7 days using safe Eloquent queries
        $data = User::select(
            DB::raw("COUNT(*) as count"),
            DB::raw("DAYNAME(created_at) as day_name"),
            DB::raw("DAY(created_at) as day")
        )
        ->where('created_at', '>', Carbon::today()->subDay(6))
        ->groupBy('day_name', 'day')
        ->orderBy('day')
        ->get();

        // Prepare data for chart display
        $array = [['Name', 'Number']];
        foreach ($data as $value) {
            $array[] = [$value->day_name, $value->count];
        }

        return $array;
    }
}