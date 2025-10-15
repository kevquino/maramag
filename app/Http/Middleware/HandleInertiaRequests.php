<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $user = $request->user();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'office' => $user->office,
                    'is_active' => $user->is_active,
                    'permissions' => $user->permissions,
                    // UNIVERSAL PERMISSION SYSTEM: Use hasPermission instead of old methods
                    'can_manage_news' => $user->hasPermission('news'),
                    'can_manage_bids_awards' => $user->hasPermission('bids_awards'),
                    'can_manage_tourism' => $user->hasPermission('tourism'),
                    'can_manage_awards_recognition' => $user->hasPermission('awards_recognition'),
                    'can_manage_sangguniang_bayan' => $user->hasPermission('sangguniang_bayan'),
                    'can_manage_full_disclosure' => $user->hasPermission('full_disclosure'),
                    'can_manage_ordinance_resolutions' => $user->hasPermission('ordinance_resolutions'),
                    'can_manage_users' => $user->hasPermission('user_management'),
                    'can_view_activity_logs' => $user->hasPermission('activity_logs'),
                    'can_manage_trash' => $user->hasPermission('trash') || $user->hasPermission('news'),
                    'can_manage_business_permit' => $user->hasPermission('business_permit'),
                    'is_admin' => $user->isAdmin(),
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}