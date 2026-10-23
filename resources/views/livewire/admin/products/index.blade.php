<div>
    <header class="flex items-end justify-between gap-4 border-b border-stone-200 pb-7">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[.18em] text-clay">Catálogo</p>
            <h1 class="mt-2 font-serif text-4xl font-semibold">Produtos</h1>
            <p class="mt-2 text-sm text-stone-600">Gerencie as peças produzidas sob demanda.</p>
        </div>
        <button type="button" wire:click="openCreate" class="grid size-12 shrink-0 place-items-center rounded-full bg-bark text-3xl font-light leading-none text-white shadow-sm transition hover:scale-105 hover:bg-clay" aria-label="Cadastrar novo produto" title="Novo produto">+</button>
    </header>

    <section class="mt-8">
        <label class="mb-5 block">
            <span class="sr-only">Pesquisar produtos</span>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome..." class="h-11 w-full rounded-sm border border-stone-300 bg-white px-4 text-sm sm:max-w-sm">
        </label>

        <div class="overflow-x-auto border border-stone-200 bg-white">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-xs uppercase tracking-wider text-stone-500">
                    <tr><th class="px-4 py-3">Produto</th><th class="px-4 py-3">Preço</th><th class="px-4 py-3">Produção</th><th class="px-4 py-3">Status</th><th class="px-4 py-3 text-right">Ações</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td class="px-4 py-4 font-medium"><div class="flex items-center gap-3">@if ($product->image_path)<img src="{{ $product->image_url }}" alt="" class="size-11 rounded object-cover">@else<div class="size-11 rounded bg-stone-100"></div>@endif<span>{{ $product->name }}</span></div></td>
                            <td class="px-4 py-4">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ $product->production_days }} dias</td>
                            <td class="px-4 py-4"><button wire:click="toggleActive({{ $product->id }})" class="rounded-full px-3 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-stone-100 text-stone-600' }}">{{ $product->is_active ? 'Ativo' : 'Inativo' }}</button></td>
                            <td class="px-4 py-4 text-right"><button wire:click="edit({{ $product->id }})" class="font-semibold text-bark">Editar</button><button wire:click="delete({{ $product->id }})" wire:confirm="Remover este produto do catálogo?" class="ml-4 font-semibold text-red-700">Excluir</button></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-stone-500">Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $products->links() }}</div>
    </section>

    <x-modal wire="productModal" size="4xl" center scrollable title="{{ $editingId ? 'Editar produto' : 'Cadastrar produto' }}">
        <form wire:submit="save" class="grid gap-5">
            <div class="grid gap-5 md:grid-cols-2">
                <x-form-field label="Nome" name="name" wire:model="name" />
                <x-form-field label="Preço (R$)" name="price" type="number" step="0.01" min="0.01" wire:model="price" />
            </div>

            <label class="grid gap-2 text-xs font-semibold">Descrição<textarea wire:model="description" rows="4" class="rounded-sm border border-ink/15 bg-paper px-4 py-3 text-sm font-normal outline-none transition focus:border-bark focus:ring-1 focus:ring-bark"></textarea>@error('description')<span class="text-xs font-normal text-red-700">{{ $message }}</span>@enderror</label>

            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <x-form-field label="Prazo de produção (dias)" name="production_days" type="number" min="1" wire:model="production_days" />
                <x-form-field label="Peso (kg)" name="weight_kg" type="number" step="0.001" wire:model="weight_kg" />
                <x-form-field label="Altura (cm)" name="height_cm" type="number" step="0.01" wire:model="height_cm" />
                <x-form-field label="Largura (cm)" name="width_cm" type="number" step="0.01" wire:model="width_cm" />
                <x-form-field label="Comprimento (cm)" name="length_cm" type="number" step="0.01" wire:model="length_cm" />
            </div>

            <label class="grid gap-2 text-xs font-semibold">Imagem<input wire:model="image" type="file" accept="image/*" class="block w-full rounded-sm border border-dashed border-ink/20 bg-stone-50 p-4 text-sm font-normal">@if ($image)<img src="{{ $image->temporaryUrl() }}" alt="Prévia da imagem" class="mt-2 aspect-video max-h-64 w-full rounded-sm object-cover">@endif @error('image')<span class="text-xs font-normal text-red-700">{{ $message }}</span>@enderror</label>

            <label class="flex items-center gap-2 text-sm"><input wire:model="is_active" type="checkbox" class="rounded text-bark focus:ring-bark"> Produto ativo</label>

            <div class="flex flex-col-reverse gap-3 border-t border-ink/10 pt-5 sm:flex-row sm:justify-end">
                <button type="button" wire:click="cancelEdit" class="rounded-full border border-stone-300 px-6 py-3 text-sm font-semibold">Cancelar</button>
                <button type="submit" class="rounded-full bg-bark px-7 py-3 text-sm font-semibold text-white transition hover:bg-clay disabled:opacity-50" wire:loading.attr="disabled"><span wire:loading.remove wire:target="save">{{ $editingId ? 'Salvar alterações' : 'Cadastrar produto' }}</span><span wire:loading wire:target="save">Salvando...</span></button>
            </div>
        </form>
    </x-modal>
</div>
