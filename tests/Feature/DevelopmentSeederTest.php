<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Product;
use App\Models\User;
use App\Services\CartService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DevelopmentSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

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

        $product = Product::query()->where('slug', 'casinha')->firstOrFail();

        $this->assertTrue($product->is_active);
        $this->assertSame('50.00', $product->price);
        $this->assertSame('products/barracao-produto-1200x1500.png', $product->image_path);

        foreach (Product::all() as $seededProduct) {
            $source = database_path('seeders/assets/'.$seededProduct->image_path);
            $this->assertFileExists($source);
            $this->assertSame(file_get_contents($source), Storage::disk('public')->get($seededProduct->image_path));
        }

        app(CartService::class)->add($product);

        $items = array_values(session('cart.items'));
        $this->assertCount(1, $items);
        $this->assertSame($product->id, $items[0]['product_id']);
        $this->assertSame(1, $items[0]['quantity']);
        $this->assertSame(5, Product::query()->where('is_active', true)->count());
        $this->assertDatabaseHas('products', ['slug' => 'favela-de-5-casas', 'price' => 250.00]);
    }

    public function test_it_can_be_executed_more_than_once_without_duplicates(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertSame(1, User::query()->where('email', 'admin@casadascasinhas.test')->count());
        $this->assertSame(1, User::query()->where('email', 'cliente@casadascasinhas.test')->count());
        $this->assertSame(1, Product::withTrashed()->where('slug', 'casinha')->count());
        $this->assertSame(5, Product::withTrashed()->count());
    }
}
