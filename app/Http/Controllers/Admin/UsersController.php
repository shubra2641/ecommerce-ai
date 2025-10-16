<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Exception;
use App\Http\Requests\Admin\UserStoreRequest;
use App\Http\Requests\Admin\UserStoreRequest as UserUpdateRequest;

/**
 * UsersController handles user management operations
 * 
 * This controller manages user creation, editing, deletion, and
 * authentication with secure validation and proper error handling.
 */
class UsersController extends Controller
{
    /**
     * Display a listing of users
     * 
     * @return View
     */
    public function index(): View
    {
        try {
            $users = User::orderBy('id', 'ASC')->paginate(10);
            return view('backend.users.index', compact('users'));
            
        } catch (Exception $e) {
            \Log::error('Error loading users: ' . $e->getMessage());
            return view('backend.users.index', ['users' => collect()]);
        }
    }

    /**
     * Show the form for creating a new user
     * 
     * @return View
     */
    public function create(): View
    {
        try {
            $roles = User::select('role')->distinct()->get();
            return view('backend.users.create', compact('roles'));
            
        } catch (Exception $e) {
            \Log::error('Error loading create user form: ' . $e->getMessage());
            abort(404, 'Create form not found');
        }
    }

    /**
     * Store a newly created user
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(UserStoreRequest $request): RedirectResponse
    {
        try {
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'email', 'role', 'status', 'photo'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));
            $data['password'] = Hash::make($validatedData['password']);

            // Create the user
            $user = User::create($data);

            if ($user) {
                request()->session()->flash('success', 'Successfully added user');
            } else {
                request()->session()->flash('error', 'Error occurred while adding user');
            }

        } catch (Exception $e) {
            \Log::error('Error storing user: ' . $e->getMessage(), [
                'request_data' => $request->only(['name', 'email', 'role'])
            ]);
            request()->session()->flash('error', 'An error occurred while creating the user');
        }

        return redirect()->route('users.index');
    }

    /**
     * Display the specified user
     * 
     * @param int $id
     * @return View
     */
    public function show(int $id): View
    {
        try {
            $user = User::findOrFail($id);
            return view('backend.users.show', compact('user'));
            
        } catch (Exception $e) {
            \Log::error('Error loading user details: ' . $e->getMessage(), [
                'user_id' => $id
            ]);
            abort(404, 'User not found');
        }
    }

    /**
     * Show the form for editing the specified user
     * 
     * @param int $id
     * @return View
     */
    public function edit(int $id): View
    {
        try {
            $user = User::findOrFail($id);
            $roles = User::select('role')->distinct()->get();
            return view('backend.users.edit', compact('user', 'roles'));
            
        } catch (Exception $e) {
            \Log::error('Error loading edit user form: ' . $e->getMessage(), [
                'user_id' => $id
            ]);
            abort(404, 'User not found');
        }
    }

    /**
     * Update the specified user
     * 
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(UserUpdateRequest $request, int $id): RedirectResponse
    {
        try {
            $user = User::findOrFail($id);
            $validatedData = $request->validated();

            // Get only allowed fields to prevent mass assignment
            $allowedFields = ['name', 'email', 'role', 'status', 'photo'];
            $data = array_intersect_key($validatedData, array_flip($allowedFields));

            // Update password if provided
            if (!empty($validatedData['password'])) {
                $data['password'] = Hash::make($validatedData['password']);
            }

            $status = $user->update($data);
            
            if ($status) {
                request()->session()->flash('success', 'Successfully updated');
            } else {
                request()->session()->flash('error', 'Error occurred while updating');
            }
            
        } catch (Exception $e) {
            \Log::error('Error updating user: ' . $e->getMessage(), [
                'user_id' => $id,
                'request_data' => $request->only(['name', 'email', 'role'])
            ]);
            request()->session()->flash('error', 'An error occurred while updating the user');
        }

        return redirect()->route('users.index');
    }

    /**
     * Remove the specified user from storage
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deletion of the last admin user
            if ($user->role === 'admin') {
                $adminCount = User::where('role', 'admin')->count();
                if ($adminCount <= 1) {
                    request()->session()->flash('error', 'Cannot delete the last admin user');
                    return redirect()->route('users.index');
                }
            }
            
            $status = $user->delete();
            
            if ($status) {
                request()->session()->flash('success', 'User successfully deleted');
            } else {
                request()->session()->flash('error', 'There is an error while deleting user');
            }
            
        } catch (Exception $e) {
            \Log::error('Error deleting user: ' . $e->getMessage(), [
                'user_id' => $id
            ]);
            request()->session()->flash('error', 'An error occurred while deleting the user');
        }

        return redirect()->route('users.index');
    }
}
