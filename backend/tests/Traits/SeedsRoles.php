<?php

namespace Tests\Traits;

use Database\Seeders\RolePermissionSeeder;

trait SeedsRoles
{
    /**
     * Seed the roles and permissions required by the application.
     */
    protected function seedRoles(): void
    {
        $this->seed(RolePermissionSeeder::class);
    }
}
