<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    /**
     * Any authenticated user with an active business may view the product list.
     */
    public function viewAny(User $user): bool
    {
        return $user->business_id !== null;
    }

    /**
     * Any authenticated user with an active business may create products.
     */
    public function create(User $user): bool
    {
        return $user->business_id !== null;
    }

    /**
     * A user may update a product only if it belongs to their business.
     * Uses strict integer comparison to prevent type-juggling bypasses.
     */
    public function update(User $user, Product $product): bool
    {
        return (int) $user->business_id === (int) $product->business_id;
    }

    /**
     * A user may delete (or request deactivation of) a product only if it belongs
     * to their business.
     */
    public function delete(User $user, Product $product): bool
    {
        return (int) $user->business_id === (int) $product->business_id;
    }
}
