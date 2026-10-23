<?php

namespace Tests\Feature;

use App\Livewire\Profile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_update_personal_information(): void
    {
        $customer = User::factory()->create();

        Livewire::actingAs($customer)->test(Profile::class)
            ->call('openEditor')
            ->set('name', 'Nome Atualizado')
            ->set('email', 'novo@example.com')
            ->call('updateProfile')
            ->assertHasNoErrors()
            ->assertSet('profileModal', false);

        $this->assertDatabaseHas('users', [
            'id' => $customer->id,
            'name' => 'Nome Atualizado',
            'email' => 'novo@example.com',
        ]);
    }

    public function test_current_password_is_required_to_change_password(): void
    {
        $customer = User::factory()->create(['password' => 'password']);

        Livewire::actingAs($customer)->test(Profile::class)
            ->call('openEditor')
            ->set('password', 'nova-senha-segura')
            ->set('password_confirmation', 'nova-senha-segura')
            ->call('updateProfile')
            ->assertHasErrors(['current_password']);

        $this->assertTrue(Hash::check('password', $customer->fresh()->password));
    }
}
