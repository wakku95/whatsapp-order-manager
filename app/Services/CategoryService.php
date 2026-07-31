<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryService
{
    /**
     * Create a new category for the current tenant.
     * business_id is injected automatically by BelongsToTenant::creating hook.
     */
    public function create(array $data): Category
    {
        return DB::transaction(function () use ($data) {
            return Category::create([
                'name'      => trim($data['name']),
                'slug'      => Str::slug(trim($data['slug'])),
                'is_active' => (bool) ($data['is_active'] ?? true),
            ]);
        });
    }

    /**
     * Update an existing category.
     * business_id is never changed — it is set at creation time only.
     */
    public function update(Category $category, array $data): Category
    {
        DB::transaction(function () use ($category, $data) {
            $category->update([
                'name'      => trim($data['name']),
                'slug'      => Str::slug(trim($data['slug'])),
                'is_active' => (bool) ($data['is_active'] ?? $category->is_active),
            ]);
        });

        return $category->fresh();
    }

    /**
     * Toggle the active/inactive state of a category.
     */
    public function toggleActive(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }

    /**
     * Delete a category only if it has no products.
     *
     * A category that still owns products must not be deleted — the owner must
     * first reassign all products to another category, or delete them, or simply
     * deactivate this category.
     *
     * @throws \RuntimeException when the category has products attached
     */
    public function delete(Category $category): void
    {
        if ($category->products()->exists()) {
            throw new \RuntimeException(
                "Cannot delete \"{$category->name}\" because it still contains products. " .
                'Reassign or delete the products first, or deactivate this category instead.'
            );
        }

        $category->delete();
    }
}
