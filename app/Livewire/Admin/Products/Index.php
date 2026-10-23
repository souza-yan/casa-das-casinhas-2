<?php

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.admin')]
class Index extends Component
{
    use AuthorizesRequests, Interactions, WithFileUploads, WithPagination;

    public string $search = '';

    public ?int $editingId = null;

    public bool $productModal = false;

    public string $name = '';

    public string $description = '';

    public string $price = '';

    public string $height_cm = '';

    public string $width_cm = '';

    public string $length_cm = '';

    public $image = null;

    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        $this->resetForm();
        $this->productModal = true;
    }

    public function edit(Product $product): void
    {
        $this->authorize('update', $product);
        $this->editingId = $product->id;
        $this->name = $product->name;
        $this->description = $product->description;
        $this->price = $product->price;
        $this->height_cm = $product->height_cm;
        $this->width_cm = $product->width_cm;
        $this->length_cm = $product->length_cm;
        $this->image = null;
        $this->resetValidation();
        $this->productModal = true;
    }

    public function save(): void
    {
        $product = $this->editingId ? Product::query()->findOrFail($this->editingId) : new Product;
        $this->authorize($product->exists ? 'update' : 'create', $product);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'price' => ['required', 'numeric', 'min:0.01', 'max:9999999999.99'],
            'height_cm' => ['required', 'numeric', 'min:0.1', 'max:999999.99'],
            'width_cm' => ['required', 'numeric', 'min:0.1', 'max:999999.99'],
            'length_cm' => ['required', 'numeric', 'min:0.1', 'max:999999.99'],
            'image' => [Rule::requiredIf(! $product->image_path), 'nullable', 'image', 'max:4096'],
        ]);

        if ($this->image) {
            $newPath = $this->image->store('products', 'public');
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $validated['image_path'] = $newPath;
        }

        unset($validated['image']);
        $validated['weight_kg'] = $product->exists ? $product->weight_kg : Product::DEFAULT_WEIGHT_KG;
        $validated['production_days'] = Product::PRODUCTION_DAYS;
        $validated['slug'] = $this->uniqueSlug($this->name, $product);
        $product->fill($validated)->save();

        $message = $this->editingId ? 'Produto atualizado.' : 'Produto cadastrado.';
        $this->resetForm();
        $this->productModal = false;
        $this->toast()->success('Alterações salvas', $message)->send();
    }

    public function delete(Product $product): void
    {
        $this->authorize('delete', $product);
        $name = $product->name;
        $product->delete();
        $this->toast()->info('Produto removido', "{$name} saiu do catálogo.")->send();
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
        $this->productModal = false;
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId', 'name', 'description', 'price', 'height_cm',
            'width_cm', 'length_cm', 'image',
        ]);
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.products.index', [
            'products' => Product::query()
                ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->latest()
                ->paginate(10),
        ]);
    }

    private function uniqueSlug(string $name, Product $product): string
    {
        $base = Str::slug($name) ?: 'produto';
        $slug = $base;
        $suffix = 2;

        while (Product::withTrashed()->where('slug', $slug)->whereKeyNot($product->getKey())->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
