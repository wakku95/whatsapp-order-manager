<?php

namespace App\Services;

use App\Models\Product;
use App\Services\TenantContext;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductService
{
    /**
     * Create a new product for the current tenant.
     *
     * Price is passed as a string/numeric value and stored as-is in the decimal(12,2) column.
     * No float arithmetic is performed here; financial calculations use BCMath in later phases.
     *
     * business_id is injected automatically by BelongsToTenant::creating hook.
     */
    public function create(array $data, ?UploadedFile $image = null): Product
    {
        return DB::transaction(function () use ($data, $image) {
            $imagePath = $image ? $this->storeImage($image) : null;

            return Product::create([
                'category_id' => $data['category_id'] ?? null,
                'name'        => trim($data['name']),
                'sku'         => isset($data['sku']) && $data['sku'] !== '' ? trim($data['sku']) : null,
                'description' => isset($data['description']) && $data['description'] !== '' ? trim($data['description']) : null,
                'price'       => $data['price'],  // string/numeric — stored as decimal(12,2)
                'stock'       => (int) $data['stock'],
                'is_active'   => (bool) ($data['is_active'] ?? true),
                'image_path'  => $imagePath,
            ]);
        });
    }

    /**
     * Update an existing product.
     *
     * Image logic:
     *  - $newImage supplied  → store new file, delete old file if present
     *  - $removeImage = true → delete old file, set image_path to null
     *  - neither             → keep existing image_path unchanged
     */
    public function update(
        Product $product,
        array $data,
        ?UploadedFile $newImage = null,
        bool $removeImage = false
    ): Product {
        DB::transaction(function () use ($product, $data, $newImage, $removeImage) {
            $imagePath = $product->image_path;

            if ($newImage) {
                // Store new image first, then delete old (keeps existing image if store fails)
                $newPath = $this->storeImage($newImage);
                $this->deleteImageFile($imagePath);
                $imagePath = $newPath;
            } elseif ($removeImage) {
                $this->deleteImageFile($imagePath);
                $imagePath = null;
            }

            $product->update([
                'category_id' => $data['category_id'] ?? null,
                'name'        => trim($data['name']),
                'sku'         => isset($data['sku']) && $data['sku'] !== '' ? trim($data['sku']) : null,
                'description' => isset($data['description']) && $data['description'] !== '' ? trim($data['description']) : null,
                'price'       => $data['price'],
                'stock'       => (int) $data['stock'],
                'is_active'   => (bool) ($data['is_active'] ?? $product->is_active),
                'image_path'  => $imagePath,
            ]);
        });

        return $product->fresh();
    }

    /**
     * Toggle the active/inactive state of a product.
     */
    public function toggleActive(Product $product): Product
    {
        $product->update(['is_active' => ! $product->is_active]);

        return $product->fresh();
    }

    /**
     * Delete a product safely.
     *
     * If the product has ever been referenced by an order item, it must NOT be
     * physically deleted — doing so would corrupt historical order data.
     * Instead it is deactivated so it no longer appears in the active catalog.
     *
     * If the product has never appeared in any order, it may be physically deleted
     * and its image file removed from storage.
     *
     * @return bool true = physically deleted, false = deactivated (has order history)
     */
    public function delete(Product $product): bool
    {
        if ($product->orderItems()->exists()) {
            // Preserve order history — deactivate only
            $product->update(['is_active' => false]);

            return false;
        }

        // Safe to physically delete
        DB::transaction(function () use ($product) {
            $this->deleteImageFile($product->image_path);
            $product->delete();
        });

        return true;
    }

    // -------------------------------------------------------------------------
    // Internal helpers
    // -------------------------------------------------------------------------

    /**
     * Store an uploaded image with a UUID filename under a business-scoped directory.
     * Files are stored in storage/app/public/products/{business_id}/
     * and served via public/storage/ after running `php artisan storage:link`.
     */
    private function storeImage(UploadedFile $image): string
    {
        $tenantId  = TenantContext::getTenantId();
        $extension = strtolower($image->getClientOriginalExtension());
        $filename  = Str::uuid() . '.' . $extension;

        return $image->storeAs("products/{$tenantId}", $filename, 'public');
    }

    /**
     * Delete an image file from the public disk if it exists.
     * Silently skips if the path is null or the file does not exist.
     */
    private function deleteImageFile(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
