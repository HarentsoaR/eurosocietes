<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UtilisateursTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_table_utilisateurs_existe_et_users_a_disparu(): void
    {
        $this->assertTrue(Schema::hasTable('utilisateurs'));
        $this->assertFalse(Schema::hasTable('users'));
    }

    public function test_user_s_ecrit_dans_utilisateurs(): void
    {
        $user = User::create([
            'name' => 'Test',
            'email' => 'test-'.uniqid().'@eurosocietes.local',
            'password' => 'ChangeMe-123!',
        ]);

        $this->assertDatabaseHas('utilisateurs', ['id' => $user->id]);
    }
}
