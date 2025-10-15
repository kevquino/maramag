<?php

namespace App\Providers;

use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);

        // Share user data with all Inertia responses
        Inertia::share([
            'auth' => function () {
                $user = Auth::user();
                
                if (!$user) {
                    return ['user' => null];
                }

                // Ensure permissions is always an array
                $permissions = $user->permissions ?? [];
                if (is_string($permissions)) {
                    try {
                        $permissions = json_decode($permissions, true) ?? [];
                    } catch (\Exception $e) {
                        $permissions = [];
                    }
                }

                return [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                        'permissions' => $permissions,
                        'office' => $user->office,
                        'is_active' => $user->is_active,
                    ],
                ];
            },
            'flash' => function () {
                return [
                    'success' => session('success'),
                    'error' => session('error'),
                    'warning' => session('warning'),
                    'info' => session('info'),
                ];
            },
        ]);
    }
}