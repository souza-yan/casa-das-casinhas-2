<div role="group" {{ $attributes->class(['flex items-center gap-1']) }} aria-label="Redes sociais">
    <a href="{{ config('social.instagram') }}" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center transition-colors hover:text-clay focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" aria-label="Instagram">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r=".8" fill="currentColor" stroke="none"/></svg>
    </a>
    <a href="{{ config('social.facebook') }}" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center transition-colors hover:text-clay focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" aria-label="Facebook">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="M14 8h3V4h-3c-3.1 0-5 2.1-5 5.2V12H6v4h3v5h4v-5h3l1-4h-4V9.5c0-.9.3-1.5 1-1.5Z"/></svg>
    </a>
    <a href="{{ config('social.whatsapp') }}" target="_blank" rel="noopener noreferrer" class="grid size-11 place-items-center transition-colors hover:text-clay focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2" aria-label="WhatsApp">
        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.5 11.6a8.5 8.5 0 0 1-12.6 7.5L3 20.5l1.4-4.7a8.5 8.5 0 1 1 16.1-4.2Z"/><path d="M8.3 7.4c-.6.1-1 1-.8 1.9.6 2.7 2.8 4.9 5.5 5.8 1 .3 2.1-.1 2.5-.8l.3-.9-2.3-1.1-.9 1a7.2 7.2 0 0 1-3.2-3.2l.9-.9-1-2Z"/></svg>
    </a>
</div>