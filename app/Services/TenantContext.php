<?php

namespace App\Services;

use App\Models\Business;

class TenantContext
{
    protected static ?Business $tenant = null;

    public static function setTenant(?Business $tenant): void
    {
        static::$tenant = $tenant;
    }

    public static function getTenant(): ?Business
    {
        if (static::$tenant !== null) {
            return static::$tenant;
        }

        if (\Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->business_id !== null) {
            static::$tenant = \Illuminate\Support\Facades\Auth::user()->business;
            return static::$tenant;
        }

        return null;
    }

    public static function getTenantId(): ?int
    {
        return static::getTenant()?->id;
    }

    public static function hasTenant(): bool
    {
        return static::getTenant() !== null;
    }

    public static function clear(): void
    {
        static::$tenant = null;
    }
}
