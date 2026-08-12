<?php

namespace App\Policies\Api;

use App\Enums\Permission;
use App\Models\Entreprise;
use App\Models\User;

class EntreprisePolicy
{
    public function viewAny(?User $user): bool
    {
        return true;
    }

    public function view(?User $user, Entreprise $entreprise): bool
    {
        return $entreprise->getRawOriginal('visible')
            || ($user && $user->hasPermissionTo(Permission::CompanyView->value));
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo(Permission::CompanyCreate->value);
    }

    public function update(User $user, Entreprise $entreprise): bool
    {
        return $user->hasPermissionTo(Permission::CompanyUpdate->value);
    }

    public function delete(User $user, Entreprise $entreprise): bool
    {
        return $user->hasPermissionTo(Permission::CompanyDelete->value);
    }
}
