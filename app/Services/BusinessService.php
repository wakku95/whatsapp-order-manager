<?php

namespace App\Services;

use App\Models\Business;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class BusinessService
{
    public function createBusinessForUser(User $user, array $data): Business
    {
        return DB::transaction(function () use ($user, $data) {
            $slug = Str::slug($data['name']);
            $originalSlug = $slug;
            $count = 1;

            while (Business::where('slug', $slug)->exists()) {
                $slug = "{$originalSlug}-" . $count++;
            }

            $business = Business::create([
                'name' => $data['name'],
                'slug' => $slug,
                'phone' => $data['phone'] ?? null,
                'currency' => strtoupper($data['currency'] ?? 'USD'),
                'status' => 'active',
            ]);

            $user->update([
                'business_id' => $business->id,
                'role' => 'owner',
            ]);

            TenantContext::setTenant($business);

            return $business;
        });
    }
}
