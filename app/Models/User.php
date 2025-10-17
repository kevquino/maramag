<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'office',
        'is_active',
        'last_login_at',
        'permissions',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'permissions' => 'array',
        ];
    }

    /**
     * Available roles - Universal System
     */
    public static function getRoles(): array
    {
        return [
            'superadmin' => 'Super Administrator',
            'admin' => 'Office Administrator',
            'staff' => 'Office Staff',
        ];
    }

    /**
     * Available offices
     */
    public static function getOffices(): array
    {
        return [
            'Mayor\'s Office' => 'Mayor\'s Office',
            'Vice Mayor\'s Office' => 'Vice Mayor\'s Office',
            'Sangguniang Bayan' => 'Sangguniang Bayan',
            'Municipal Planning and Development Office' => 'Municipal Planning and Development Office',
            'Municipal Accounting Office' => 'Municipal Accounting Office',
            'Municipal Treasurer\'s Office' => 'Municipal Treasurer\'s Office',
            'Municipal Budget Office' => 'Municipal Budget Office',
            'Municipal Assessor\'s Office' => 'Municipal Assessor\'s Office',
            'Municipal Engineer\'s Office' => 'Municipal Engineer\'s Office',
            'Municipal Health Office' => 'Municipal Health Office',
            'Municipal Agriculture Office' => 'Municipal Agriculture Office',
            'Municipal Social Welfare and Development Office' => 'Municipal Social Welfare and Development Office',
            'Public Information Office' => 'Public Information Office',
            'Municipal Disaster Risk Reduction and Management Office' => 'Municipal Disaster Risk Reduction and Management Office',
            'Bids and Awards Committee' => 'Bids and Awards Committee',
            'Tourism Office' => 'Tourism Office',
            'Business Permit and Licensing Office' => 'Business Permit and Licensing Office',
            'Other' => 'Other',
        ];
    }

    /**
     * Available permissions
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'dashboard' => [
                'label' => 'Dashboard',
                'description' => 'Access to dashboard and overview'
            ],
            'news' => [
                'label' => 'News Management',
                'description' => 'Create, edit, and manage news articles'
            ],
            'bids_awards' => [
                'label' => 'Bids & Awards',
                'description' => 'Manage bids and awards information'
            ],
            'full_disclosure' => [
                'label' => 'Full Disclosure',
                'description' => 'Access full disclosure documents'
            ],
            'tourism' => [
                'label' => 'Tourism Management',
                'description' => 'Manage tourism packages and information'
            ],
            'awards_recognition' => [
                'label' => 'Awards & Recognition',
                'description' => 'Manage awards and recognition'
            ],
            'sangguniang_bayan' => [
                'label' => 'Sangguniang Bayan',
                'description' => 'Access Sangguniang Bayan information'
            ],
            'ordinance_resolutions' => [
                'label' => 'Ordinance & Resolutions',
                'description' => 'Manage ordinances and resolutions'
            ],
            'user_management' => [
                'label' => 'User Management',
                'description' => 'Manage system users (Super Admin only)'
            ],
            'activity_logs' => [
                'label' => 'Activity Logs',
                'description' => 'View system activity logs'
            ],
            'trash' => [
                'label' => 'Trash Management',
                'description' => 'Restore or permanently delete items'
            ],
            'business_permit' => [
                'label' => 'Business Permit',
                'description' => 'Manage business permit applications'
            ],
            'new_application' => [
                'label' => 'New Application',
                'description' => 'Process new business permit applications'
            ],
            'renewal_permit' => [
                'label' => 'Renewal Permit',
                'description' => 'Process business permit renewals'
            ],
        ];
    }

    /**
     * UNIVERSAL PERMISSION SYSTEM
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        // Superadmin has all permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin and Staff: Check database permissions array
        $permissions = $this->permissions ?? [];
        
        // Ensure permissions is always treated as array
        if (is_string($permissions)) {
            try {
                $permissions = json_decode($permissions, true) ?? [];
            } catch (\Exception $e) {
                $permissions = [];
            }
        }
        
        return in_array($permission, $permissions);
    }

    /**
     * UNIVERSAL PERMISSION METHODS
     */

    /**
     * Check if user can manage news
     */
    public function canManageNews(): bool
    {
        return $this->hasPermission('news');
    }

    /**
     * Check if user can manage bids and awards
     */
    public function canManageBidsAwards(): bool
    {
        return $this->hasPermission('bids_awards');
    }

    /**
     * Check if user can manage tourism
     */
    public function canManageTourism(): bool
    {
        return $this->hasPermission('tourism');
    }

    /**
     * Check if user can manage awards and recognition
     */
    public function canManageAwardsRecognition(): bool
    {
        return $this->hasPermission('awards_recognition');
    }

    /**
     * Check if user can manage sangguniang bayan
     */
    public function canManageSangguniangBayan(): bool
    {
        return $this->hasPermission('sangguniang_bayan');
    }

    /**
     * Check if user can manage full disclosure
     */
    public function canManageFullDisclosure(): bool
    {
        return $this->hasPermission('full_disclosure');
    }

    /**
     * Check if user can manage ordinance resolutions
     */
    public function canManageOrdinanceResolutions(): bool
    {
        return $this->hasPermission('ordinance_resolutions');
    }

    /**
     * Check if user can manage users
     */
    public function canManageUsers(): bool
    {
        return $this->hasPermission('user_management') || $this->isSuperAdmin();
    }

    /**
     * Check if user can view activity logs
     */
    public function canViewActivityLogs(): bool
    {
        return $this->hasPermission('activity_logs') || $this->isSuperAdmin();
    }

    /**
     * Check if user can manage trash
     */
    public function canManageTrash(): bool
    {
        return $this->hasPermission('trash');
    }

    /**
     * Check if user can manage business permits
     */
    public function canManageBusinessPermit(): bool
    {
        return $this->hasPermission('business_permit');
    }

    /**
     * Check if user can process new applications
     */
    public function canProcessNewApplications(): bool
    {
        return $this->hasPermission('new_application');
    }

    /**
     * Check if user can process renewals
     */
    public function canProcessRenewals(): bool
    {
        return $this->hasPermission('renewal_permit');
    }

    /**
     * Check if user is superadmin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is staff
     */
    public function isStaff(): bool
    {
        return $this->role === 'staff';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->is_active ?? true;
    }

    /**
     * Get user's permissions with labels
     */
    public function getPermissionsWithLabels(): array
    {
        $availablePermissions = self::getAvailablePermissions();
        $userPermissions = $this->permissions ?? [];
        
        if (is_string($userPermissions)) {
            try {
                $userPermissions = json_decode($userPermissions, true) ?? [];
            } catch (\Exception $e) {
                $userPermissions = [];
            }
        }
        
        $permissionsWithLabels = [];
        foreach ($userPermissions as $permission) {
            if (isset($availablePermissions[$permission])) {
                $permissionsWithLabels[$permission] = $availablePermissions[$permission];
            }
        }
        
        return $permissionsWithLabels;
    }

    /**
     * Get default permissions based on role and office
     * This is only used when creating new users
     */
    public static function getDefaultPermissions(string $role, string $office): array
    {
        $defaultPermissions = ['dashboard']; // All users get dashboard access

        // Office-based permissions
        $officePermissions = self::getOfficeDefaultPermissions($office);
        $defaultPermissions = array_merge($defaultPermissions, $officePermissions);

        return array_unique($defaultPermissions);
    }

    /**
     * Get office-based default permissions
     */
    public static function getOfficeDefaultPermissions(string $office): array
    {
        $permissions = ['trash']; // All offices get trash access by default

        switch ($office) {
            case 'Public Information Office':
                $permissions = array_merge($permissions, ['news']);
                break;
            case 'Bids and Awards Committee':
                $permissions = array_merge($permissions, ['bids_awards']);
                break;
            case 'Municipal Planning and Development Office':
                $permissions = array_merge($permissions, ['full_disclosure']);
                break;
            case 'Tourism Office':
                $permissions = array_merge($permissions, ['tourism', 'full_disclosure']);
                break;
            case 'Mayor\'s Office':
                $permissions = array_merge($permissions, ['news', 'tourism', 'awards_recognition']);
                break;
            case 'Business Permit and Licensing Office':
                $permissions = array_merge($permissions, ['business_permit', 'new_application', 'renewal_permit']);
                break;
            case 'Vice Mayor\'s Office':
            case 'Sangguniang Bayan':
                $permissions = array_merge($permissions, ['sangguniang_bayan', 'ordinance_resolutions']);
                break;
        }

        return array_unique($permissions);
    }

    /**
     * Scope a query to only include active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include users with specific permission.
     */
    public function scopeWithPermission($query, string $permission)
    {
        return $query->where(function($q) use ($permission) {
            $q->where('role', 'superadmin')
              ->orWhereJsonContains('permissions', $permission);
        });
    }

    /**
     * Get users by office
     */
    public function scopeByOffice($query, string $office)
    {
        return $query->where('office', $office);
    }

    /**
     * Get users by role
     */
    public function scopeByRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Check if user has any of the given permissions
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if user has all of the given permissions
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Add permission to user
     */
    public function addPermission(string $permission): bool
    {
        // Superadmin doesn't need explicit permissions
        if ($this->isSuperAdmin()) {
            return true;
        }

        $permissions = $this->permissions ?? [];
        
        if (is_string($permissions)) {
            try {
                $permissions = json_decode($permissions, true) ?? [];
            } catch (\Exception $e) {
                $permissions = [];
            }
        }
        
        if (!in_array($permission, $permissions)) {
            $permissions[] = $permission;
            $this->permissions = $permissions;
            return $this->save();
        }
        
        return true;
    }

    /**
     * Remove permission from user
     */
    public function removePermission(string $permission): bool
    {
        // Cannot remove permissions from superadmin
        if ($this->isSuperAdmin()) {
            return false;
        }

        $permissions = $this->permissions ?? [];
        
        if (is_string($permissions)) {
            try {
                $permissions = json_decode($permissions, true) ?? [];
            } catch (\Exception $e) {
                $permissions = [];
            }
        }
        
        $key = array_search($permission, $permissions);
        if ($key !== false) {
            unset($permissions[$key]);
            $this->permissions = array_values($permissions);
            return $this->save();
        }
        
        return true;
    }

    /**
     * Sync user permissions
     */
    public function syncPermissions(array $permissions): bool
    {
        // Cannot change permissions for superadmin
        if ($this->isSuperAdmin()) {
            return false;
        }

        // Ensure dashboard is always included
        if (!in_array('dashboard', $permissions)) {
            $permissions[] = 'dashboard';
        }

        $this->permissions = array_values(array_unique($permissions));
        return $this->save();
    }

    /**
     * Get permission groups for organized display
     */
    public static function getPermissionGroups(): array
    {
        return [
            'content_management' => [
                'label' => 'Content Management',
                'permissions' => ['news', 'bids_awards', 'tourism', 'awards_recognition']
            ],
            'document_management' => [
                'label' => 'Document Management',
                'permissions' => ['full_disclosure', 'ordinance_resolutions']
            ],
            'system_management' => [
                'label' => 'System Management',
                'permissions' => ['user_management', 'activity_logs', 'trash']
            ],
            'services' => [
                'label' => 'Services',
                'permissions' => ['business_permit', 'new_application', 'renewal_permit', 'sangguniang_bayan']
            ],
            'general' => [
                'label' => 'General',
                'permissions' => ['dashboard']
            ]
        ];
    }

    /**
     * Get user's permissions grouped by category
     */
    public function getGroupedPermissions(): array
    {
        $groups = self::getPermissionGroups();
        $userPermissions = $this->permissions ?? [];
        
        if (is_string($userPermissions)) {
            try {
                $userPermissions = json_decode($userPermissions, true) ?? [];
            } catch (\Exception $e) {
                $userPermissions = [];
            }
        }
        
        $grouped = [];
        foreach ($groups as $groupKey => $group) {
            $grouped[$groupKey] = [
                'label' => $group['label'],
                'permissions' => []
            ];
            
            foreach ($group['permissions'] as $permission) {
                if (in_array($permission, $userPermissions) || $this->hasPermission($permission)) {
                    $grouped[$groupKey]['permissions'][$permission] = self::getAvailablePermissions()[$permission];
                }
            }
        }
        
        return $grouped;
    }

    /**
     * Check if current session is impersonating
     */
    public static function isImpersonating(): bool
    {
        return session()->has('impersonate.original_user_id');
    }

    /**
     * Get original user ID when impersonating
     */
    public static function getOriginalUserId(): ?int
    {
        return session()->get('impersonate.original_user_id');
    }

    /**
     * Check if user can be impersonated (not superadmin and not self)
     */
    public function canBeImpersonated(): bool
    {
        return !$this->isSuperAdmin() && $this->id !== auth()->id();
    }

    /**
     * Get the user's last login date in a readable format
     */
    public function getLastLoginFormatted(): string
    {
        if (!$this->last_login_at) {
            return 'Never';
        }

        return $this->last_login_at->format('M j, Y g:i A');
    }

    /**
     * Get the user's creation date in a readable format
     */
    public function getCreatedAtFormatted(): string
    {
        return $this->created_at->format('M j, Y');
    }

    /**
     * Check if user has verified email
     */
    public function hasVerifiedEmail(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * Get user's status badge color
     */
    public function getStatusBadgeVariant(): string
    {
        return $this->is_active ? 'default' : 'secondary';
    }

    /**
     * Get user's role badge color
     */
    public function getRoleBadgeVariant(): string
    {
        switch ($this->role) {
            case 'superadmin': return 'destructive';
            case 'admin': return 'default';
            case 'staff': return 'secondary';
            default: return 'outline';
        }
    }

    /**
     * Check if user can edit another user
     */
    public function canEditUser(User $targetUser): bool
    {
        // Superadmin can edit any user
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin can edit users in their office (except superadmin)
        if ($this->isAdmin() && $this->office === $targetUser->office && !$targetUser->isSuperAdmin()) {
            return true;
        }

        // Users can edit their own profile
        return $this->id === $targetUser->id;
    }

    /**
     * Check if user can delete another user
     */
    public function canDeleteUser(User $targetUser): bool
    {
        // Only superadmin can delete users
        if (!$this->isSuperAdmin()) {
            return false;
        }

        // Cannot delete yourself
        return $this->id !== $targetUser->id;
    }

    /**
     * Check if user can change status of another user
     */
    public function canChangeUserStatus(User $targetUser): bool
    {
        // Superadmin can change any user's status
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Admin can change status of users in their office (except superadmin)
        if ($this->isAdmin() && $this->office === $targetUser->office && !$targetUser->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can impersonate another user
     */
    public function canImpersonateUser(User $targetUser): bool
    {
        // Only superadmin can impersonate
        if (!$this->isSuperAdmin()) {
            return false;
        }

        // Cannot impersonate yourself or other superadmins
        return $this->id !== $targetUser->id && !$targetUser->isSuperAdmin();
    }

    /**
     * Get office-based permissions suggestions
     */
    public static function getOfficePermissionSuggestions(string $office): array
    {
        return self::getOfficeDefaultPermissions($office);
    }
}