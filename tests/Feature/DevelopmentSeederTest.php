<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_demo_users_and_a_purchasable_product(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseHas('users', [
            'email' => 'admin@casadascasinhas.test',
            'role' => Role::ADMIN->value,
        ]);
        $this->assertDatabaseHas('users', [
            'email' => 'cliente@casadascasinhas.test',
            'role' => Role::CUSTOMER->value,
        ]);

        $product = Product::query()->where('slug', 'casas-da-vila')->firstOrFail();

        $this->assertTrue($product->is_active);
        $this->assertSame('289.90', $product->price);
        $this->assertFileExists(public_path($product->image_path));

        app(CartService::class)->add($product);

        $this->assertSame([$product->id => 1], session('cart.items'));
        $this->assertSame(6, Product::query()->where('is_active', true)->count());
        $this->assertDatabaseHas('products', ['slug' => 'favela-de-5-casas', 'price' => 250.00]);
    }

    public function test_it_can_be_executed_more_than_once_without_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'admin@casadascasinhas.test')->count());
        $this->assertSame(1, User::query()->where('email', 'cliente@casadascasinhas.test')->count());
        $this->assertSame(1, Product::withTrashed()->where('slug', 'casas-da-vila')->count());
        $this->assertSame(6, Product::withTrashed()->count());
    }
}
