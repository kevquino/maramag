<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\News;
use App\Models\Activity;
use App\Models\BidsAward;
use App\Models\FullDisclosure;
use App\Models\TourismPackage;
use App\Models\AwardsRecognition;
use App\Models\SangguniangBayanMember;
use App\Models\OrdinanceResolution;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response
    {
        // Check if user has permission to manage users or is superadmin
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $filters = $request->only(['search', 'role', 'status', 'office']);
        
        $users = User::query()
            ->select([
                'id', 'name', 'email', 'phone', 'role', 'office', 'position', 
                'avatar', 'is_active', 'last_login_at', 'email_verified_at',
                'created_at', 'updated_at'
            ])
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

        // Get badge counts for the sidebar/navigation
        $badgeCounts = $this->getBadgeCounts(auth()->user());

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
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        // Check if user has permission to manage users or is superadmin
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $badgeCounts = $this->getBadgeCounts(auth()->user());

        return Inertia::render('UserManagement/Create', [
            'roleOptions' => User::getRoles(),
            'officeOptions' => User::getOffices(),
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // Check if user has permission to manage users or is superadmin
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin()) {
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

        $user = User::create($userData);

        // Log activity
        Activity::create([
            'description' => "User created: {$user->name} ({$user->email})",
            'type' => 'user_management',
            'user_id' => auth()->id(),
            'metadata' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'office' => $user->office,
                'action' => 'created'
            ]
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): Response
    {
        $user = User::findOrFail($id);
        
        // Users can view their own profile, admins/superadmins can view any
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $badgeCounts = $this->getBadgeCounts(auth()->user());

        return Inertia::render('UserManagement/Show', [
            'user' => $user,
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): Response
    {
        $user = User::findOrFail($id);
        
        // Users can edit their own profile, admins/superadmins can edit any
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $badgeCounts = $this->getBadgeCounts(auth()->user());

        // Check if current user can edit permissions for this user
        $canEditPermissions = (auth()->user()->canManageUsers() || auth()->user()->isSuperAdmin()) && 
                             auth()->id() !== $user->id;

        return Inertia::render('UserManagement/Edit', [
            'user' => $user,
            'roleOptions' => User::getRoles(),
            'officeOptions' => User::getOffices(),
            'permissionOptions' => User::getAvailablePermissions(),
            'permissionGroups' => User::getPermissionGroups(),
            'canEditPermissions' => $canEditPermissions,
            'badgeCounts' => $badgeCounts,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Users can update their own profile, admins/superadmins can update any
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin() && auth()->id() !== $user->id) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'position' => 'nullable|string|max:255',
            'role' => ['required', 'string', Rule::in(array_keys(User::getRoles()))],
            'office' => ['required', 'string', Rule::in(array_keys(User::getOffices()))],
            'is_active' => 'boolean',
            'password' => 'nullable|string|min:8|confirmed',
            'permissions' => 'array',
            'permissions.*' => ['string', Rule::in(array_keys(User::getAvailablePermissions()))],
            'avatar' => 'nullable|string',
        ]);

        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? $user->phone,
            'position' => $validated['position'] ?? $user->position,
            'role' => $validated['role'],
            'office' => $validated['office'],
            'is_active' => $validated['is_active'] ?? $user->is_active,
        ];

        // Update avatar if provided
        if (isset($validated['avatar'])) {
            $updateData['avatar'] = $validated['avatar'];
        }

        // Update permissions if provided (only for admin/superadmin and not editing self)
        if ((auth()->user()->canManageUsers() || auth()->user()->isSuperAdmin()) && 
            auth()->id() !== $user->id && 
            isset($validated['permissions'])) {
            
            // If user is being set as superadmin, automatically grant all permissions
            if ($validated['role'] === 'superadmin') {
                $updateData['permissions'] = array_keys(User::getAvailablePermissions());
            } else {
                $updateData['permissions'] = $validated['permissions'];
            }
        }

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Log activity
        Activity::create([
            'description' => "User updated: {$user->name} ({$user->email})",
            'type' => 'user_management',
            'user_id' => auth()->id(),
            'metadata' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'office' => $user->office,
                'action' => 'updated'
            ]
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Check if user has permission to manage users or is superadmin
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Prevent users from deleting themselves
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot delete your own account.');
        }

        $userName = $user->name;
        $userEmail = $user->email;

        $user->delete();

        // Log activity
        Activity::create([
            'description' => "User deleted: {$userName} ({$userEmail})",
            'type' => 'user_management',
            'user_id' => auth()->id(),
            'metadata' => [
                'user_id' => $user->id,
                'name' => $userName,
                'email' => $userEmail,
                'action' => 'deleted'
            ]
        ]);

        return redirect()->route('user-management.index')
            ->with('success', 'User deleted successfully.');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Check if user has permission to manage users or is superadmin
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin()) {
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

        // Log activity
        Activity::create([
            'description' => "User {$status}: {$user->name} ({$user->email})",
            'type' => 'user_management',
            'user_id' => auth()->id(),
            'metadata' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'action' => 'status_toggled',
                'is_active' => $user->is_active
            ]
        ]);

        return redirect()->back()
            ->with('success', "User {$status} successfully.");
    }

    /**
     * Resend email verification
     */
    public function resendVerification(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Check if user has permission to manage users or is superadmin
        if (!auth()->user()->canManageUsers() && !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Check if email is already verified
        if ($user->hasVerifiedEmail()) {
            return redirect()->back()
                ->with('error', 'Email is already verified.');
        }

        // Send verification notification
        $user->sendEmailVerificationNotification();

        // Log activity
        Activity::create([
            'description' => "Verification email resent: {$user->name} ({$user->email})",
            'type' => 'user_management',
            'user_id' => auth()->id(),
            'metadata' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'action' => 'verification_resent'
            ]
        ]);

        return redirect()->back()
            ->with('success', 'Verification email sent successfully.');
    }

    /**
     * Impersonate user
     */
    public function impersonate(string $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        // Only superadmin can impersonate
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        // Cannot impersonate yourself
        if ($user->id === auth()->id()) {
            return redirect()->back()
                ->with('error', 'You cannot impersonate yourself.');
        }

        // Store original user ID in session
        session()->put('impersonate.original_user_id', auth()->id());
        
        // Log in as the target user
        auth()->login($user);

        // Log activity
        Activity::create([
            'description' => "Started impersonating user: {$user->name} ({$user->email})",
            'type' => 'user_management',
            'user_id' => session()->get('impersonate.original_user_id'),
            'metadata' => [
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'action' => 'impersonate_started'
            ]
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'Now impersonating ' . $user->name);
    }

    /**
     * Stop impersonating
     */
    public function stopImpersonate(): RedirectResponse
    {
        if (!User::isImpersonating()) {
            return redirect()->route('dashboard')
                ->with('error', 'Not currently impersonating any user.');
        }

        $originalUserId = User::getOriginalUserId();
        
        if ($originalUserId) {
            $originalUser = User::find($originalUserId);
            auth()->login($originalUser);
        }

        // Log activity
        Activity::create([
            'description' => "Stopped impersonating user",
            'type' => 'user_management',
            'user_id' => $originalUserId,
            'metadata' => [
                'action' => 'impersonate_stopped'
            ]
        ]);

        session()->forget('impersonate.original_user_id');

        return redirect()->route('user-management.index')
            ->with('success', 'Stopped impersonating user.');
    }

    /**
     * Get badge counts based on user permissions
     */
    private function getBadgeCounts(User $user): array
    {
        $badgeCounts = [];

        // Debug: Check user permissions
        \Log::debug('UserManagementController - User permissions check', [
            'user_id' => $user->id,
            'is_superadmin' => $user->isSuperAdmin(),
            'is_admin' => $user->isAdmin(),
            'permissions' => $user->permissions,
        ]);

        // Superadmin and users with specific permissions get badge counts
        if ($user->isSuperAdmin() || $user->hasPermission('news')) {
            $badgeCounts['news'] = News::where('status', 'published')->count();
            
            // Get trash count - News model uses SoftDeletes
            $trashCount = News::onlyTrashed()->count();
            $badgeCounts['trash'] = $trashCount;
        }

        if ($user->isSuperAdmin() || $user->hasPermission('bids_awards')) {
            $badgeCounts['bids_awards'] = BidsAward::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('full_disclosure')) {
            $badgeCounts['full_disclosure'] = FullDisclosure::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('tourism')) {
            $badgeCounts['tourism'] = TourismPackage::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('awards_recognition')) {
            $badgeCounts['awards_recognition'] = AwardsRecognition::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('sangguniang_bayan')) {
            $badgeCounts['sangguniang_bayan'] = SangguniangBayanMember::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('ordinance_resolutions')) {
            $badgeCounts['ordinance_resolutions'] = OrdinanceResolution::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('user_management')) {
            $badgeCounts['users'] = User::count();
        }

        if ($user->isSuperAdmin() || $user->hasPermission('activity_logs')) {
            $badgeCounts['activity_logs'] = Activity::count();
        }

        // Ensure superadmin gets all badge counts even if they don't have explicit permissions
        if ($user->isSuperAdmin()) {
            // Make sure all badge counts are set
            $allBadges = [
                'news' => News::where('status', 'published')->count(),
                'bids_awards' => BidsAward::count(),
                'full_disclosure' => FullDisclosure::count(),
                'tourism' => TourismPackage::count(),
                'awards_recognition' => AwardsRecognition::count(),
                'sangguniang_bayan' => SangguniangBayanMember::count(),
                'ordinance_resolutions' => OrdinanceResolution::count(),
                'users' => User::count(),
                'activity_logs' => Activity::count(),
                'trash' => News::onlyTrashed()->count(),
            ];
            
            $badgeCounts = array_merge($allBadges, $badgeCounts);
        }

        // Debug: Final badge counts
        \Log::debug('UserManagementController - Final badge counts', $badgeCounts);

        return $badgeCounts;
    }
}