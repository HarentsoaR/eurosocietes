<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolePermissionSeeder::class);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@eurosocietes.local',
            'password' => 'ChangeMe-123!',
        ])->assignRole(Role::Admin);

        User::factory()->create([
            'name' => 'Utilisateur',
            'email' => 'user@eurosocietes.local',
            'password' => 'ChangeMe-123!',
        ])->assignRole(Role::User);
    }
}
