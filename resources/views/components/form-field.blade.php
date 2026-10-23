@props(['label', 'name', 'type' => 'text', 'value' => null])

<label class="grid gap-2 text-xs font-semibold" for="{{ $name }}">
    <span>{{ $label }}@if ($attributes->has('required')) <span class="text-red-700" aria-hidden="true">*</span>@endif</span>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? null : $value }}" {{ $attributes->merge(['class' => 'h-12 rounded-sm border border-ink/15 bg-paper px-4 text-sm font-normal outline-none transition placeholder:text-ink/30 focus:border-bark focus:ring-1 focus:ring-bark']) }}>
    @error($name)<span class="text-xs font-normal text-red-700">{{ $message }}</span>@enderror
</label>
