@extends('layouts.guest', ['title' => 'Criar conta — Casa das Casinhas'])

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#8b5e3c]">Sua conta</p>
    <h1 class="mt-3 font-serif text-4xl tracking-[-0.04em]">Crie seu cadastro</h1>
    <p class="mt-3 text-sm leading-6 text-[#261d17]/60">Use seus dados para comprar e acompanhar encomendas.</p>
    <form method="POST" action="{{ route('register') }}" class="mt-8 grid gap-5">
        @csrf
        <x-form-field label="Nome" name="name" autocomplete="name" :value="old('name')" required autofocus />
        <x-form-field label="E-mail" name="email" type="email" autocomplete="email" :value="old('email')" required />
        <x-form-field label="Senha" name="password" type="password" autocomplete="new-password" required />
        <x-form-field label="Confirme a senha" name="password_confirmation" type="password" autocomplete="new-password" required />
        <button type="submit" class="h-12 bg-[#4b2d1b] px-5 text-sm font-semibold uppercase tracking-[0.12em] text-white transition hover:bg-[#684028]">Criar conta</button>
    </form>
    <p class="mt-7 text-center text-sm text-[#261d17]/60">Já tem uma conta? <a href="{{ route('login') }}" class="font-semibold text-[#8b5e3c] hover:underline">Entrar</a></p>
@endsection
