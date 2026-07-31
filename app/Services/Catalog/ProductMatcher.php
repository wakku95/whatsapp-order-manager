<?php

namespace App\Services\Catalog;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class ProductMatcher
{
    /**
     * Match a query string against the current tenant's active products.
     *
     * @param string $query
     * @return array ['status' => 'single'|'multiple'|'none', 'product' => ?Product, 'matches' => Collection]
     */
    public function match(string $query): array
    {
        $normalizedQuery = Str::lower(trim($query));
        
        if (empty($normalizedQuery)) {
            return ['status' => 'none', 'product' => null, 'matches' => collect()];
        }

        // Active products for current business (TenantScope applies automatically)
        $activeProducts = Product::where('is_active', true)->get();

        if ($activeProducts->isEmpty()) {
            return ['status' => 'none', 'product' => null, 'matches' => collect()];
        }

        // 1. Exact Match (Normalized)
        $exactMatches = $activeProducts->filter(function (Product $p) use ($normalizedQuery) {
            return Str::lower(trim($p->name)) === $normalizedQuery;
        });

        if ($exactMatches->count() === 1) {
            return ['status' => 'single', 'product' => $exactMatches->first(), 'matches' => $exactMatches];
        }

        // 2. Strong Partial Match (Product name starts with query or contains exact word)
        $partialMatches = $activeProducts->filter(function (Product $p) use ($normalizedQuery) {
            $name = Str::lower(trim($p->name));
            return Str::startsWith($name, $normalizedQuery) || preg_match('/\b' . preg_quote($normalizedQuery, '/') . '\b/i', $name);
        });

        if ($partialMatches->count() === 1) {
            return ['status' => 'single', 'product' => $partialMatches->first(), 'matches' => $partialMatches];
        }

        if ($partialMatches->count() > 1) {
            return ['status' => 'multiple', 'product' => null, 'matches' => $partialMatches->values()];
        }

        // 3. Substring / Broad Fuzzy Match
        $fuzzyMatches = $activeProducts->filter(function (Product $p) use ($normalizedQuery) {
            return Str::contains(Str::lower($p->name), $normalizedQuery);
        });

        if ($fuzzyMatches->count() === 1) {
            return ['status' => 'single', 'product' => $fuzzyMatches->first(), 'matches' => $fuzzyMatches];
        }

        if ($fuzzyMatches->count() > 1) {
            return ['status' => 'multiple', 'product' => null, 'matches' => $fuzzyMatches->values()];
        }

        return ['status' => 'none', 'product' => null, 'matches' => collect()];
    }
}
