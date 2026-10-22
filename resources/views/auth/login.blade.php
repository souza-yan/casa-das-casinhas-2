@extends('layouts.guest', ['title' => 'Entrar — Casa das Casinhas'])

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#8b5e3c]">Bem-vindo de volta</p>
    <h1 class="mt-3 font-serif text-4xl tracking-[-0.04em]">Entre na sua conta</h1>
    <p class="mt-3 text-sm leading-6 text-[#261d17]/60">Acompanhe seus pedidos e continue suas compras.</p>

    @if (session('status'))
        <div class="mt-6 border border-green-700/20 bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="mt-8 grid gap-5">
        @csrf
        <x-form-field label="E-mail" name="email" type="email" autocomplete="email" :value="old('email')" required autofocus />
        <x-form-field label="Senha" name="password" type="password" autocomplete="current-password" required />
        <div class="flex items-center justify-between gap-4 text-sm">
            <label class="flex items-center gap-2"><input name="remember" type="checkbox" class="rounded border-[#261d17]/30 text-[#4b2d1b] focus:ring-[#8b5e3c]"> Lembrar de mim</label>
            <a href="{{ route('password.request') }}" class="font-semibold text-[#8b5e3c] hover:underline">Esqueci a senha</a>
        </div>
        <button type="submit" class="h-12 bg-[#4b2d1b] px-5 text-sm font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-[#684028]">Entrar</button>
    </form>
    <p class="mt-7 text-center text-sm text-[#261d17]/60">Ainda não tem conta? <a href="{{ route('register') }}" class="font-semibold text-[#8b5e3c] hover:underline">Cadastre-se</a></p>
@endsection
