<?php

namespace App\Policies;

use App\Models\News;
use App\Models\User;

class NewsPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageNews();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, News $news): bool
    {
        return $user->canManageNews();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->canManageNews();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, News $news): bool
    {
        return $user->canManageNews();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, News $news): bool
    {
        return $user->isAdmin() || $user->isPioOfficer();
    }

    /**
     * Determine whether the user can feature/unfeature the model.
     */
    public function feature(User $user, News $news): bool
    {
        return $user->isAdmin() || $user->isPioOfficer();
    }

    /**
     * Determine whether the user can publish the model.
     */
    public function publish(User $user, News $news): bool
    {
        return $user->isAdmin() || $user->isPioOfficer();
    }
}