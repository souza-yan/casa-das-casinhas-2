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
            ->set('weight_kg', '0.750')
            ->set('height_cm', '20')
            ->set('width_cm', '15')
            ->set('length_cm', '12')
            ->set('production_days', 10)
            ->set('image', UploadedFile::fake()->image('casinha.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $product = Product::query()->sole();
        $this->assertSame('casinha-azul', $product->slug);
        $this->assertSame('129.90', $product->price);
        Storage::disk('public')->assertExists($product->image_path);
    }

    public function test_product_fields_and_image_are_validated(): void
    {
        Storage::fake('public');

        Livewire::actingAs(User::factory()->admin()->create())->test(Index::class)
            ->set('name', '')
            ->set('price', '-1')
            ->set('weight_kg', '0')
            ->set('production_days', 0)
            ->set('image', UploadedFile::fake()->create('produto.pdf', 50, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['name', 'description', 'price', 'weight_kg', 'height_cm', 'width_cm', 'length_cm', 'production_days', 'image']);
    }

    public function test_admin_can_update_toggle_and_soft_delete_product(): void
    {
        $admin = User::factory()->admin()->create();
        $product = Product::factory()->create(['name' => 'Original']);

        Livewire::actingAs($admin)->test(Index::class)
            ->call('edit', $product->id)
            ->set('name', 'Produto Editado')
            ->call('save')
            ->assertHasNoErrors()
            ->call('toggleActive', $product->id)
            ->call('delete', $product->id);

        $this->assertSoftDeleted($product);
        $this->assertFalse(Product::withTrashed()->findOrFail($product->id)->is_active);
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
