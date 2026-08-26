<?php

namespace App\Filament\Concerns;

/**
 * Gates the four standard resource actions against Spatie permissions named
 * "{action}_{permissionKey}" (see database/seeders/RolePermissionSeeder).
 * A resource whose class name doesn't map cleanly to its permission slug
 * (e.g. UserResource -> "user") should override permissionKey().
 */
trait HasPermissionGates
{
    public static function permissionKey(): string
    {
        return str(static::getModel())
            ->classBasename()
            ->snake()
            ->toString();
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('view_'.static::permissionKey()) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create_'.static::permissionKey()) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can('update_'.static::permissionKey()) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can('delete_'.static::permissionKey()) ?? false;
    }
}
