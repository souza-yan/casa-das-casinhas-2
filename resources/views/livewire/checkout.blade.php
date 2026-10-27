<div class="mx-auto max-w-[1120px] px-5 py-12 sm:px-8 lg:py-20">
    <p class="text-sm font-semibold uppercase tracking-[.22em] text-clay">Compra segura</p>
    <h1 class="mt-2 font-serif text-4xl font-semibold tracking-[-.04em] sm:text-6xl">Finalizar compra</h1>

    @error('cart')<p class="mt-4 text-sm text-red-700">{{ $message }}</p>@enderror
    <ol class="mt-7 grid max-w-3xl gap-2 text-center text-xs font-semibold uppercase tracking-[.1em] sm:gap-3 sm:text-sm" style="grid-template-columns: repeat(4, minmax(0, 1fr));">
        @foreach ([1 => 'Dados pessoais', 2 => 'Dados de entrega', 3 => 'Escolha a entrega', 4 => 'Pagamento'] as $number => $label)
            <li class="flex flex-col items-center gap-2 {{ $step >= $number ? 'text-ink' : 'text-ink/35' }}">
                <span class="grid size-8 place-items-center rounded-full {{ $step >= $number ? 'bg-ink text-paper' : 'border border-ink/20' }}">{{ $number }}</span>
                <span>{{ $label }}</span>
            </li>
        @endforeach
    </ol>

    <form wire:submit="place" class="mt-10 grid gap-12 lg:grid-cols-[1fr_350px]">
        <section>
            @if ($step === 1)
                <div class="border-b border-ink/10 pb-5"><h2 class="font-serif text-3xl font-semibold">Dados pessoais</h2><p class="mt-1 text-base text-ink/45">Usaremos estas informações apenas para processar seu pedido.</p></div>
                <div class="mt-7 grid gap-5 sm:grid-cols-2"><x-form-field label="Nome completo" name="name" wire:model="name" minlength="3" x-on:input="$el.value = $el.value.replace(/[^\p{L}\p{M}\s'’-]/gu, '')" required /><x-form-field label="E-mail" name="email" type="email" wire:model="email" inputmode="email" x-on:input="$el.value = $el.value.trim().toLowerCase()" required /><x-form-field label="Telefone" name="phone" wire:model="phone" inputmode="tel" maxlength="15" pattern="\(\d{2}\) \d{4,5}-\d{4}" placeholder="(00) 00000-0000" x-on:input="const digits = $el.value.replace(/\D/g, '').slice(0, 11); $el.value = digits.length > 2 ? '(' + digits.slice(0, 2) + ') ' + digits.slice(2, digits.length > 10 ? 7 : 6) + (digits.length > 6 ? '-' + digits.slice(digits.length > 10 ? 7 : 6) : '') : digits; $wire.set('phone', $el.value, false)" required /><x-form-field label="CPF ou CNPJ" name="document" wire:model="document" inputmode="numeric" maxlength="18" placeholder="000.000.000-00" x-on:input="const digits = $el.value.replace(/\D/g, '').slice(0, 14); $el.value = digits.length <= 11 ? digits.replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d{1,2})$/, '$1-$2') : digits.replace(/(\d{2})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1.$2').replace(/(\d{3})(\d)/, '$1/$2').replace(/(\d{4})(\d{1,2})$/, '$1-$2'); $wire.set('document', $el.value, false)" required /></div>
                @guest<p class="mt-4 text-sm text-ink/55">Já tem uma conta? <a href="{{ route('login') }}" class="font-semibold text-ink underline underline-offset-4">Entrar</a></p>@endguest
                <x-action-button type="button" wire:click="continueToDelivery" class="mt-8 w-full sm:w-auto">Continuar para entrega</x-action-button>
            @elseif ($step === 2)
                <div class="border-b border-ink/10 pb-5"><h2 class="font-serif text-3xl font-semibold">Dados de entrega</h2><p class="mt-1 text-base text-ink/45">Informe onde sua peça deve chegar.</p></div>
                <div class="mt-7 grid gap-5 sm:grid-cols-2">
                    <x-form-field label="CEP" name="zip_code" wire:model.live.debounce.500ms="zip_code" inputmode="numeric" autocomplete="postal-code" maxlength="9" pattern="\d{5}-\d{3}" placeholder="00000-000" x-on:input="const digits = $el.value.replace(/\D/g, '').slice(0, 8); $el.value = digits.length > 5 ? digits.slice(0, 5) + '-' + digits.slice(5) : digits" required />
                </div>
                <p wire:loading wire:target="zip_code,lookupAddress,quote" class="mt-3 text-sm text-ink/60" role="status">Buscando endereço e opções de entrega...</p>
                @if ($postalCodeError)
                    <div class="mt-3 text-sm text-red-700" role="alert"><p>{{ $postalCodeError }}</p><button type="button" wire:click="lookupAddress" wire:loading.attr="disabled" class="mt-2 underline underline-offset-4">Tentar novamente</button></div>
                @endif
                @if ($showAddressFields)
                    <div class="mt-5 grid gap-5 sm:grid-cols-2" wire:key="delivery-address-{{ $zip_code }}">
                        <x-form-field label="Rua" name="street" wire:model="street" autocomplete="address-line1" :readonly="filled($resolvedAddress['street'] ?? null)" minlength="3" required />
                        <x-form-field label="Bairro" name="district" wire:model="district" :readonly="filled($resolvedAddress['district'] ?? null)" minlength="2" required />
                        <x-form-field label="Cidade" name="city" wire:model="city" autocomplete="address-level2" :readonly="filled($resolvedAddress['city'] ?? null)" minlength="2" required />
                        <x-form-field label="UF" name="state" wire:model="state" autocomplete="address-level1" :readonly="filled($resolvedAddress['state'] ?? null)" maxlength="2" pattern="[A-Z]{2}" x-on:input="$el.value = $el.value.replace(/[^a-zA-Z]/g, '').toUpperCase().slice(0, 2)" required />
                        <x-form-field label="Número" name="number" wire:model="number" maxlength="30" required />
                        <x-form-field label="Complemento" name="complement" wire:model="complement" maxlength="255" placeholder="Apartamento, bloco... (opcional)" />
                    </div>
                @endif
                @if ($error)
                    <div class="mt-4 text-sm text-red-700" role="alert"><p>{{ $error }}</p><button type="button" wire:click="lookupAddress" wire:loading.attr="disabled" class="mt-2 underline underline-offset-4">Tentar novamente</button></div>
                @endif
                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between"><x-action-button type="button" wire:click="goToStep(1)">Voltar</x-action-button><x-action-button type="button" wire:click="continueToShipping" wire:loading.attr="disabled" wire:target="continueToShipping,quote">Continuar para entregas</x-action-button></div>
            @elseif ($step === 3)
                <div class="border-b border-ink/10 pb-5"><h2 class="font-serif text-3xl font-semibold">Escolha a entrega</h2><p class="mt-1 text-base text-ink/45">Selecione a melhor opção de transporte para seu pedido.</p></div>
                @if ($shippingOptions)<fieldset class="mt-7 grid gap-3"><legend class="sr-only">Opções de entrega</legend>@foreach ($shippingOptions as $option)<label class="flex cursor-pointer items-center justify-between gap-4 border border-ink/10 bg-paper p-4 transition has-[:checked]:border-ink"><span class="flex items-center gap-3 text-base"><input wire:model.live="shippingId" type="radio" value="{{ $option['id'] }}" class="text-ink focus:ring-ink"><span>{{ $option['carrier'] }} — {{ $option['name'] }}<small class="mt-1 block text-ink/45">Transporte: {{ $option['delivery_days'] }} dias úteis</small></span></span><strong class="text-base">R$ {{ number_format($option['price'], 2, ',', '.') }}</strong></label>@endforeach</fieldset>@else<div class="mt-7 border border-clay/30 bg-clay/10 p-4 text-base text-ink">Não encontramos opções de entrega. Volte e confira o endereço.</div>@endif
                @error('shippingId')<p class="mt-3 text-base text-red-700">{{ $message }}</p>@enderror
                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between"><x-action-button type="button" wire:click="goToStep(2)">Voltar</x-action-button><x-action-button type="button" wire:click="continueToPayment">Continuar para pagamento</x-action-button></div>
            @else
                <div class="border-b border-ink/10 pb-5"><h2 class="font-serif text-3xl font-semibold">Pagamento</h2><p class="mt-1 text-base text-ink/45">Revise seu pedido antes de seguir para o ambiente seguro de pagamento.</p></div>
                <div class="mt-7 space-y-5 text-base"><div><span class="block text-sm text-ink/45">Dados pessoais</span><strong class="mt-1 block">{{ $name }}</strong><span>{{ $email }} · {{ $phone }} · {{ $document }}</span></div><div class="border-t border-ink/10 pt-5"><span class="block text-sm text-ink/45">Entrega</span><strong class="mt-1 block">{{ $street }}, {{ $number }}</strong><span>{{ $district }} · {{ $city }}/{{ $state }} · {{ $zip_code }}</span></div>@if ($shippingId)<div class="border-t border-ink/10 pt-5"><span class="block text-sm text-ink/45">Frete selecionado</span><strong class="mt-1 block">{{ collect($shippingOptions)->firstWhere('id', $shippingId)['name'] ?? 'Opção selecionada' }}</strong></div>@endif</div>
                <div class="mt-8 flex flex-col-reverse gap-3 sm:flex-row sm:justify-between"><x-action-button type="button" wire:click="goToStep(3)">Voltar</x-action-button><x-action-button type="submit" wire:loading.attr="disabled"><span wire:loading.remove wire:target="place">Prosseguir para pagamento</span><span wire:loading wire:target="place">Criando pedido...</span></x-action-button></div>
                <p class="mt-4 text-sm leading-5 text-ink/40">Você será direcionado ao ambiente seguro de pagamento. Criar uma conta é opcional.</p>
            @endif
        </section>

        <aside class="h-fit border border-ink/10 bg-cream p-6 sm:p-8">
            <div class="divide-y divide-ink/10">
                @foreach ($items as $item)
                    <div class="flex justify-between gap-4 py-3 text-base"><span class="text-ink/60">{{ $item['quantity'] }}× {{ $item['product']->name }}</span><strong class="shrink-0">R$ {{ number_format($item['subtotal'], 2, ',', '.') }}</strong></div>
                @endforeach
            </div>
            @if ($productionDays > 0)
                <p class="mt-4 flex justify-between gap-4 border-t border-ink/10 pt-4 text-sm"><span class="text-ink/55">Produção</span><span>Até {{ $productionDays }} dias úteis</span></p>
            @endif
            <div class="mt-4 flex justify-between border-t border-ink/10 pt-4"><span>Subtotal</span><strong>R$ {{ number_format($subtotal, 2, ',', '.') }}</strong></div>
            @if ($selectedShipping)
                <p class="mt-3 flex justify-between text-base"><span class="text-ink/55">Frete</span><span>R$ {{ number_format($selectedShipping['price'], 2, ',', '.') }}</span></p>
                <div class="mt-4 flex justify-between border-t border-ink/10 pt-4"><strong>Total</strong><strong>R$ {{ number_format($subtotal + $selectedShipping['price'], 2, ',', '.') }}</strong></div>
            @endif
        </aside>
    </form>
</div>
