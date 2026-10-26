@extends('layouts.guest', ['title' => 'Entrar — Casa das Casinhas'])

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.22em] text-clay">Bem-vindo de volta</p>
    <h1 class="mt-3 font-serif text-4xl font-semibold tracking-[-0.04em] sm:text-6xl">Entre na sua conta</h1>
    <p class="mt-3 text-base leading-6 text-[#2a1b13]/60">Acompanhe seus pedidos e continue suas compras.</p>

    @if (session('status'))
        <div class="mt-6 border border-green-700/20 bg-green-50 p-3 text-base text-green-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 grid gap-5">
        @csrf
        <x-form-field label="E-mail" name="email" type="email" autocomplete="email" :value="old('email')" required autofocus />
        <x-form-field label="Senha" name="password" type="password" autocomplete="current-password" required />
        <div class="flex items-center justify-between gap-4 text-base">
            <label class="flex items-center gap-2"><input name="remember" type="checkbox" class="rounded border-[#2a1b13]/30 text-[#4a2a16] focus:ring-[#4a2a16]"> Lembrar de mim</label>
            <a href="{{ route('password.request') }}" class="font-semibold text-[#4a2a16] hover:underline">Esqueci a senha</a>
        </div>
        <x-action-button type="submit" class="w-full">Entrar</x-action-button>
    </form>
    <p class="mt-7 text-center text-base text-[#2a1b13]/60">Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-[#4a2a16] hover:underline">Cadastre-se</a></p>
@endsection
