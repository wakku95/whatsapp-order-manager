<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;

class CategoryPolicy
{
    /**
     * Any authenticated user with an active business may view the category list.
     */
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    /**
     * Any authenticated user with an active business may create categories.
     */
    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    /**
     * A user may update a category only if it belongs to their business.
     * Uses strict integer comparison to prevent type-juggling bypasses.
     */
    public function update(User $user, Category $category): bool
    {
        return (int) $user->business_id === (int) $category->business_id;
    }

    /**
     * A user may delete a category only if it belongs to their business.
     */
    public function delete(User $user, Category $category): bool
    {
        return (int) $user->business_id === (int) $category->business_id;
    }
}
