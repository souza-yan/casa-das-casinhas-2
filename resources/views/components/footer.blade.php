<footer class="border-t border-paper/15 bg-ink text-paper">
    <div class="mx-auto max-w-[1400px] px-5 py-8 sm:px-8 lg:px-12 lg:py-10">
        <div class="flex flex-col gap-7 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-lg">
                <a href="{{ route('home') }}" class="font-serif text-2xl tracking-[-.04em]">Casa das Casinhas</a>
                <p class="mt-2 text-sm leading-6 text-paper/65">Casas e paisagens de madeira montadas e pintadas à mão, disponíveis em estoque ou sob encomenda.</p>
            </div>

            <x-social-links />
        </div>

        <div class="mt-7 flex flex-col gap-2 border-t border-paper/15 pt-5 text-sm text-paper/50 sm:flex-row sm:items-center sm:justify-between">
            <span>© {{ date('Y') }} Casa das Casinhas</span>
            <span>Peças artesanais em estoque e sob demanda</span>
        </div>
    </div>
</footer>
