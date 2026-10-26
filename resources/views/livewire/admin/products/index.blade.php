<div>
    <header class="flex items-end justify-between gap-4 border-b border-stone-200 pb-7">
        <div>
            <p class="text-sm font-semibold uppercase tracking-[.18em] text-clay">Catálogo</p>
            <h1 class="mt-2 font-serif text-4xl font-semibold">Produtos</h1>
            <p class="mt-2 text-sm text-stone-600">Gerencie as peças produzidas sob demanda.</p>
        </div>
        <button type="button" wire:click="openCreate" class="grid size-12 shrink-0 place-items-center rounded-full bg-bark text-3xl font-light leading-none text-paper shadow-sm transition hover:scale-105 hover:bg-clay" aria-label="Cadastrar novo produto" title="Novo produto">+</button>
    </header>

    <section class="mt-8">
        <label class="mb-5 block">
            <span class="sr-only">Pesquisar produtos</span>
            <input wire:model.live.debounce.300ms="search" type="search" placeholder="Pesquisar por nome..." class="h-11 w-full rounded-sm border border-stone-300 bg-paper px-4 text-sm sm:max-w-sm">
        </label>

        <div class="overflow-x-auto border border-stone-200 bg-paper">
            <table class="w-full min-w-[680px] text-left text-sm">
                <thead class="border-b border-stone-200 bg-stone-50 text-sm uppercase tracking-wider text-stone-500">
                    <tr><th class="px-4 py-3">Produto</th><th class="px-4 py-3">Preço</th><th class="px-4 py-3">Produção</th><th class="px-4 py-3 text-right">Ações</th></tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse ($products as $product)
                        <tr wire:key="product-{{ $product->id }}">
                            <td class="px-4 py-4 font-medium"><div class="flex items-center gap-3"><x-product-image :product="$product" variant="admin" /><span>{{ $product->name }}</span></div></td>
                            <td class="px-4 py-4">R$ {{ number_format((float) $product->price, 2, ',', '.') }}</td>
                            <td class="px-4 py-4">{{ $product->production_days }} dias</td>
                            <td class="px-4 py-4"><div class="flex justify-end gap-1"><button wire:click="edit({{ $product->id }})" class="grid size-9 place-items-center rounded-full text-bark transition hover:bg-sand/60" aria-label="Editar {{ $product->name }}" title="Editar produto"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M13.5 6.5 17.5 10.5M4 20l4.2-1 10.6-10.6a2.8 2.8 0 0 0-4-4L4.2 15 4 20Z"/></svg></button><button wire:click="delete({{ $product->id }})" wire:confirm="Remover este produto do catálogo?" class="grid size-9 place-items-center rounded-full text-red-700 transition hover:bg-red-50" aria-label="Excluir {{ $product->name }}" title="Excluir produto"><svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V4h6v3M7 7l1 13h8l1-13M10 11v5M14 11v5"/></svg></button></div></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-stone-500">Nenhum produto encontrado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-5">{{ $products->links() }}</div>
    </section>

    <x-modal wire="productModal" size="3xl" center scrollable title="{{ $editingId ? 'Editar produto' : 'Cadastrar produto' }}">
        <form wire:submit="save" class="product-form grid gap-4 text-sm">
            <div class="grid gap-4 md:grid-cols-2">
                <x-form-field label="Nome" name="name" wire:model="name" class="h-10 text-sm" required />
                <x-form-field label="Preço (R$)" name="price" type="number" inputmode="decimal" step="0.01" min="0.01" wire:model="price" class="h-10 text-sm" required />
            </div>

            <label class="grid gap-1.5 text-sm font-semibold"><span>Descrição <span class="text-red-700" aria-hidden="true">*</span></span><textarea wire:model="description" rows="3" required class="rounded-sm border border-ink/15 bg-paper px-3 py-2 text-sm font-normal outline-none transition focus:border-bark focus:ring-1 focus:ring-bark"></textarea>@error('description')<span class="text-sm font-normal text-red-700">{{ $message }}</span>@enderror</label>

            <div>
                <div class="mb-2 flex items-center justify-between"><p class="text-[10px] font-semibold uppercase tracking-[.14em] text-ink/45">Dimensões da peça</p><span class="text-[10px] text-ink/40">Produção padrão: 15 dias</span></div>
                <div class="grid grid-cols-3 gap-3">
                    <x-form-field label="Altura (cm)" name="height_cm" type="number" inputmode="decimal" step="0.01" wire:model="height_cm" class="h-10 px-3 text-sm" required />
                    <x-form-field label="Largura (cm)" name="width_cm" type="number" inputmode="decimal" step="0.01" wire:model="width_cm" class="h-10 px-3 text-sm" required />
                    <x-form-field label="Comprimento (cm)" name="length_cm" type="number" inputmode="decimal" step="0.01" wire:model="length_cm" class="h-10 px-3 text-sm" required />
                </div>
            </div>

            <label class="flex items-start gap-3 rounded-sm border border-ink/15 bg-paper p-3 text-sm"><input wire:model="is_customizable" type="checkbox" class="mt-1 rounded border-ink/30 text-ink focus:ring-ink"><span><strong class="block">Aceita personalização</strong><span class="mt-1 block font-normal text-ink/55">Exige que o cliente descreva as preferências antes de adicionar esta peça ao carrinho.</span></span></label>

            <label class="grid gap-1.5 text-sm font-semibold"><span>Imagem @if (! $editingId)<span class="text-red-700" aria-hidden="true">*</span>@endif</span><span class="text-xs font-normal text-ink/50">Use imagens em proporção 4:5 (1200 x 1500 px), JPG ou WebP, até 2 MB.</span><input wire:model="image" type="file" accept="image/jpeg,image/png,image/webp" @required(! $editingId) class="block w-full rounded-sm border border-dashed border-ink/20 bg-stone-50 p-3 text-sm font-normal">@if ($image)<x-product-image :src="$image->temporaryUrl()" alt="Prévia da imagem" variant="admin" :preview="true" class="mt-2 max-h-80" />@endif @error('image')<span class="text-sm font-normal text-red-700">{{ $message }}</span>@enderror</label>

            <div class="flex flex-col-reverse gap-2 border-t border-ink/10 pt-4 sm:flex-row sm:justify-end">
                <button type="button" wire:click="cancelEdit" class="rounded-full border border-stone-300 px-5 py-2.5 text-sm font-semibold">Cancelar</button>
                <button type="submit" class="rounded-full bg-bark px-6 py-2.5 text-sm font-semibold text-paper transition hover:bg-clay disabled:opacity-50" wire:loading.attr="disabled"><span wire:loading.remove wire:target="save">{{ $editingId ? 'Salvar alterações' : 'Cadastrar produto' }}</span><span wire:loading wire:target="save">Salvando...</span></button>
            </div>
        </form>
    </x-modal>
</div>
