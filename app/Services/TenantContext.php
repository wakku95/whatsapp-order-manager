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
        return static::$tenant;
    }

    public static function getTenantId(): ?int
    {
        return static::$tenant?->id;
    }

    public static function hasTenant(): bool
    {
        return static::$tenant !== null;
    }

    public static function clear(): void
    {
        static::$tenant = null;
    }
}
