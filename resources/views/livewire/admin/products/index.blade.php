<div>
    <header class="flex flex-col gap-4 border-b border-stone-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div><p class="text-xs font-semibold uppercase tracking-[.18em] text-[#1d4ed8]">Catálogo</p><h1 class="mt-2 text-3xl font-semibold">Produtos</h1><p class="mt-2 text-sm text-stone-600">Gerencie as peças produzidas sob demanda.</p></div>
        <button type="button" wire:click="cancelEdit" class="bg-[#1d4ed8] px-5 py-3 text-sm font-semibold text-white">Novo produto</button>
    </header>

    @if (session('status'))<div class="mt-6 border border-green-200 bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>@endif

    <div class="mt-8 grid gap-8 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section>
            <label class="mb-5 block"><span class="sr-only">Pesquisar produtos</span><input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome..." class="h-11 w-full border border-stone-300 bg-white px-4 text-sm sm:max-w-sm"></label>
            <div class="overflow-x-auto border border-stone-200 bg-white">
                <table class="w-full min-w-[680px] text-left text-sm">
                    <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500"><tr><th class="px-4 py-3">Produto</th><th class="px-4 py-3">Preço</th><th class="px-4 py-3">Produção</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Ações</th></tr></thead>
                    <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td class="px-4 py-4 font-medium"><div class="flex items-center gap-3">@if($product->image_path)<img src="{{ Storage::url($product->image_path) }}" alt="" class="size-11 rounded object-cover">@else<div class="size-11 rounded bg-slate-100"></div>@endif<span>{{ $product->name }}</span></div></td><td class="px-4 py-4">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td><td class="px-4 py-4">{{ $product->production_days }} dias</td>
                            <td class="px-4 py-4"><button wire:click="toggleActive({{ $product->id }})" class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-stone-600' }}">{{ $product->is_active ? 'Ativo' : 'Inativo' }}</button></td>
                            <td class="px-4 py-4 text-right"><button wire:click="edit({{ $product->id }})" class="font-semibold text-[#1d4ed8]">Editar</button><button wire:click="delete({{ $product->id }})" wire:confirm="Remover este produto do catálogo?" class="ml-4 font-semibold text-red-700">Excluir</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-stone-500">Nenhum produto encontrado.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-5">{{ $products->links() }}</div>
        </section>

        <section class="border border-stone-200 bg-white p-5 sm:p-6">
            <h2 class="text-lg font-semibold">{{ $editingId ? 'Editar produto' : 'Cadastrar produto' }}</h2>
            <form wire:submit="save" class="mt-5 grid gap-4">
                <x-form-field label="Nome" name="name" wire:model="name" />
                <label class="grid gap-2 text-sm font-medium">Descrição<textarea wire:model="description" rows="4" class="border border-stone-300 px-3 py-2"></textarea>@error('description')<span class="text-red-700">{{ $message }}</span>@enderror</label>
                <div class="grid grid-cols-2 gap-3"><x-form-field label="Preço (R$)" name="price" type="number" step="0.01" min="0.01" wire:model="price" /><x-form-field label="Prazo (dias)" name="production_days" type="number" min="1" wire:model="production_days" /></div>
                <div class="grid grid-cols-2 gap-3"><x-form-field label="Peso (kg)" name="weight_kg" type="number" step="0.001" wire:model="weight_kg" /><x-form-field label="Altura (cm)" name="height_cm" type="number" step="0.01" wire:model="height_cm" /><x-form-field label="Largura (cm)" name="width_cm" type="number" step="0.01" wire:model="width_cm" /><x-form-field label="Comprimento (cm)" name="length_cm" type="number" step="0.01" wire:model="length_cm" /></div>
                <label class="grid gap-2 text-sm font-medium">Imagem <input wire:model="image" type="file" accept="image/*" class="block w-full text-sm">@if($image)<img src="{{ $image->temporaryUrl() }}" alt="Prévia da imagem" class="mt-2 aspect-video w-full rounded object-cover">@endif @error('image')<span class="text-red-700">{{ $message }}</span>@enderror</label>
                <label class="flex items-center gap-2 text-sm"><input wire:model="is_active" type="checkbox" class="rounded text-[#1d4ed8]"> Produto ativo</label>
                <div class="flex gap-3 pt-2"><button type="submit" class="flex-1 bg-[#1d4ed8] px-4 py-3 text-sm font-semibold text-white" wire:loading.attr="disabled">Salvar</button>@if($editingId)<button type="button" wire:click="cancelEdit" class="border border-stone-300 px-4 py-3 text-sm">Cancelar</button>@endif</div>
            </form>
        </section>
    </div>
</div>
