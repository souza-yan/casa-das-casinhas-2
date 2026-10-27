@props(['label', 'name', 'type' => 'text', 'value' => null])

<label x-data="{ fieldChanged: false }" x-on:input="fieldChanged = true" class="grid gap-2 text-sm font-semibold" for="{{ $name }}">
    <span>{{ $label }}@if ($attributes->has('required')) <span class="text-red-700" aria-hidden="true">*</span>@endif</span>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? null : $value }}" {{ $attributes->merge(['class' => 'h-12 border border-clay bg-paper px-4 text-base font-normal outline-none transition placeholder:text-ink/30 focus:border-terracotta focus:ring-1 focus:ring-terracotta']) }}>
    @error($name)<span x-show="! fieldChanged" class="text-sm font-normal text-red-700">{{ $message }}</span>@enderror
</label>
