<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $filters = $request->only(['search', 'role', 'status', 'office']);
        
        $users = User::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', '%'.$search.'%')
                          ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['role'] ?? null, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($filters['office'] ?? null, function ($query, $office) {
                $query->where('office', $office);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('is_active', $filters['status'] === 'active');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('UserManagement/Index', [
            'users' => $users,
            'filters' => $filters,
            'roleOptions' => User::getRoles(),
            'officeOptions' => User::getOffices(),
            'statusOptions' => [
                'active' => 'Active',
                'inactive' => 'Inactive',
            ],
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('UserManagement/Create', [
            'roleOptions' => User::getRoles(),
            'officeOptions' => User::getOffices(),
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => ['required', 'string', Rule::in(array_keys(User::getRoles()))],
            'office' => ['required', 'string', Rule::in(array_keys(User::getOffices()))],
            'is_active' => 'boolean',
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::in(array_keys(User::getAvailablePermissions()))],
        ]);

        $userData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'office' => $validated['office'],
            'is_active' => $validated['is_active'] ?? true,
            'last_login_at' => null,
        ];

        // Add permissions if provided, otherwise set default permissions
        if (isset($validated['permissions'])) {
            $userData['permissions'] = $validated['permissions'];
        } else {
            // Set default permissions based on role and office
            $userData['permissions'] = User::getDefaultPermissions(
                $validated['role'], 
                $validated['office']
            );
        }

        User::create($userData);

        return redirect()->route('user-management.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        
        // Users can view their own profile, admins can view any
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('UserManagement/Show', [
            'user' => $user,
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        
        // Users can edit their own profile, admins can edit any
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        return Inertia::render('UserManagement/Edit', [
            'user' => $user,
            'roleOptions' => User::getRoles(),
            'officeOptions' => User::getOffices(),
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Users can update their own profile, admins can update any
        if (!auth()->user()->isAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'string', Rule::in(array_keys(User::getRoles()))],
            'office' => ['required', 'string', Rule::in(array_keys(User::getOffices()))],
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::in(array_keys(User::getAvailablePermissions()))],
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'office' => $validated['office'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        // Update permissions if provided (only for admin)
        if (auth()->user()->isAdmin() && isset($validated['permissions'])) {
            $updateData['permissions'] = $validated['permissions'];
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        return redirect()->route('user-management.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('user-management.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent users from deactivating themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot deactivate your own account.');
        }

        $user->update([
            'is_active' => !$user->is_active,
        ]);

        $status = $user->is_active ? 'activated' : 'deactivated';

        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Resend email verification
     */
    public function resendVerification(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Check if user is admin
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('error', 'Email is already verified.');
        }

        // Send verification notification
        $user->sendEmailVerificationNotification();

        return redirect()->back()
            ->with('success', 'Verification email sent successfully.');
    }
}