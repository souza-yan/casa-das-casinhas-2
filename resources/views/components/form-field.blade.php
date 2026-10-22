@props(['label', 'name', 'type' => 'text', 'value' => null])

<label class="grid gap-2 text-sm font-medium" for="{{ $name }}">
    <span>{{ $label }}</span>
    <input id="{{ $name }}" name="{{ $name }}" type="{{ $type }}" value="{{ $type === 'password' ? null : $value }}" {{ $attributes->merge(['class' => 'h-12 border border-[#2a1b13]/20 bg-white/60 px-4 outline-none transition focus:border-[#4a2a16] focus:ring-1 focus:ring-[#4a2a16]']) }}>
    @error($name)<span class="text-sm font-normal text-red-700">{{ $message }}</span>@enderror
</label>
