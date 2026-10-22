<?php

namespace Tests\Feature\Auth;

use App\Enums\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $this->get('/register')->assertOk()->assertSee('Crie seu cadastro');
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Maria Artesã',
            'email' => 'maria@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'maria@example.com',
            'role' => Role::CUSTOMER->value,
        ]);
    }

    public function test_public_registration_cannot_define_admin_role(): void
    {
        $this->post('/register', [
            'name' => 'Usuário malicioso',
            'email' => 'customer@example.com',
            'password' => 'senha-segura',
            'password_confirmation' => 'senha-segura',
            'role' => Role::ADMIN->value,
        ])->assertRedirect('/dashboard');

        $this->assertDatabaseHas('users', [
            'email' => 'customer@example.com',
            'role' => Role::CUSTOMER->value,
        ]);
    }
}
