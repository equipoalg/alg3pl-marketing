<?php

namespace App\Filament\Traits;

trait HasRoleAccess
{
    public static function canAccess(): bool
    {
        $user = auth()->user();
        if (!$user) return false;
        if ($user->is_super_admin || $user->role === 'admin') return true;

        $allowed = static::getAllowedRoles();
        return in_array($user->role, $allowed);
    }

    public static function getAllowedRoles(): array
    {
        return ['admin', 'manager', 'sales', 'viewer'];
    }
}
