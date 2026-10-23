<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_profile_dashboard_redirects_to_administration(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('dashboard'))
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_admin_is_redirected_away_from_customer_purchase_pages(): void
    {
        $admin = User::factory()->admin()->create();

        foreach ([route('cart'), route('checkout'), route('orders.index')] as $route) {
            $this->actingAs($admin)->get($route)->assertRedirect(route('admin.dashboard'));
        }
    }

    public function test_storefront_hides_customer_actions_from_admin(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('store'))
            ->assertOk()
            ->assertSee('Painel administrativo')
            ->assertDontSee('Carrinho');
    }
}
