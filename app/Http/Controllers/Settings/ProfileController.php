<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();

        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $user instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'userData' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'role' => $user->role,
                'office' => $user->office,
                'position' => $user->position,
                'avatar' => $user->avatar,
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
                'last_login_ip' => $user->last_login_ip,
                'login_count' => $user->login_count,
                'permissions' => $user->permissions,
                'timezone' => $user->timezone,
                'locale' => $user->locale,
                'email_verified_at' => $user->email_verified_at,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'formOptions' => [
                'roles' => User::getRoles(),
                'offices' => User::getOffices(),
                'positions' => User::getPositions(),
            ]
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        
        // Handle avatar - empty string means use initials, null means no change
        if ($request->has('avatar')) {
            $avatarValue = $request->input('avatar');
            
            // If avatar is empty string, set to null (use initials)
            if ($avatarValue === '') {
                // Delete old avatar file if it exists and is stored locally
                if ($user->avatar && Storage::exists($user->avatar)) {
                    Storage::delete($user->avatar);
                }
                $user->avatar = null;
            } 
            // If avatar is a valid image path, set it
            elseif (!empty($avatarValue) && is_string($avatarValue)) {
                $user->avatar = $avatarValue;
            }
            // If avatar is null, don't change it (keep current avatar)
        }
        
        // Update other profile fields (excluding role)
        $user->fill($request->only([
            'name',
            'email',
            'phone',
            'office',
            'position',
            'timezone',
            'locale',
        ]));

        // Handle email verification reset if email changed
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit')->with('status', 'Profile updated successfully.');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        // Delete user's avatar if stored locally
        if ($user->avatar && Storage::exists($user->avatar)) {
            Storage::delete($user->avatar);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}