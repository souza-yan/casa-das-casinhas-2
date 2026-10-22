@props(['label', 'name', 'type' => 'text', 'value' => null])

<label class="grid gap-2 text-sm font-medium" for="{{ $name }}">
    <span>{{ $label }}</span>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? null : $value }}" {{ $attributes->merge(['class' => 'h-12 border border-[#261d17]/20 bg-white/60 px-4 outline-none transition focus:border-[#8b5e3c] focus:ring-1 focus:ring-[#8b5e3c]']) }}>
    @error($name)<span class="text-sm font-normal text-red-700">{{ $message }}</span>@enderror
</label>
