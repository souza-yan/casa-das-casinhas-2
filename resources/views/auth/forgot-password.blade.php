@extends('layouts.guest', ['title' => 'Recuperar senha — Casa das Casinhas'])

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-clay">Acesso</p>
    <h1 class="mt-3 font-serif text-4xl font-semibold tracking-[-0.04em] sm:text-6xl">Recupere sua senha</h1>
    <p class="mt-3 text-base leading-6 text-[#2a1b13]/60">Enviaremos um link de redefinição para seu e-mail.</p>
    @if (session('status'))<div class="mt-6 border border-green-700/20 bg-green-50 p-3 text-base text-green-800">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 grid gap-5">
        @csrf
        <x-form-field label="E-mail" name="email" type="email" autocomplete="email" :value="old('email')" required autofocus />
        <x-action-button type="submit" class="w-full">Enviar link</x-action-button>
    </form>
@endsection
