<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\Products\Index;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class ProductManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_admin_can_access_product_management(): void
    {
        $this->get(route('admin.products.index'))->assertRedirect(route('login'));
        $this->actingAs(User::factory()->create())->get(route('admin.products.index'))->assertForbidden();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.products.index'))->assertOk();
    }

    public function test_admin_can_create_product_with_image(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        Livewire::actingAs($admin)->test(Index::class)
            ->set('name', 'Casinha Azul')
            ->set('description', 'Peça artesanal produzida sob demanda.')
            ->set('price', '129.90')
            ->set('height_cm', '20')
            ->set('width_cm', '15')
            ->set('length_cm', '12')
            ->set('image', UploadedFile::fake()->image('casinha.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->sole();
        $this->assertSame('casinha-azul', $product->slug);
        $this->assertSame('129.90', $product->price);
        $this->assertSame('1.000', $product->weight_kg);
        $this->assertSame(Product::PRODUCTION_DAYS, $product->production_days);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_fields_and_image_are_validated(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->admin()->create())->test(Index::class)
            ->set('name', '')
            ->set('price', '-1')
            ->set('image', UploadedFile::fake()->create('produto.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['name', 'description', 'price', 'height_cm', 'width_cm', 'length_cm', 'image']);
    }

    public function test_admin_can_update_and_soft_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Original', 'weight_kg' => 2.5, 'production_days' => 8]);

        Livewire::actingAs($admin)->test(Index::class)
            ->call('edit', $product->id)
            ->set('name', 'Produto Editado')
            ->call('save')
            ->assertHasNoErrors()
            ->call('delete', $product->id);

        $this->assertSoftDeleted($product);
        $this->assertSame('2.500', Product::withTrashed()->findOrFail($product->id)->weight_kg);
        $this->assertSame(Product::PRODUCTION_DAYS, Product::withTrashed()->findOrFail($product->id)->production_days);
    }

    public function test_products_can_be_searched_by_name(): void
    {
        Product::factory()->create(['name' => 'Casinha Verde']);
        Product::factory()->create(['name' => 'Porta-chaves']);

        Livewire::actingAs(User::factory()->admin()->create())->test(Index::class)
            ->set('search', 'Verde')
            ->assertSee('Casinha Verde')
            ->assertDontSee('Porta-chaves');
    }
}
