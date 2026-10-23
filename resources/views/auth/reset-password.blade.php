@extends('layouts.guest', ['title' => 'Nova senha — Casa das Casinhas'])

@section('content')
    <p class="text-[10px] font-semibold uppercase tracking-[0.22em] text-clay">Acesso</p>
    <h1 class="mt-3 font-serif text-5xl font-semibold tracking-[-0.04em]">Defina uma nova senha</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
        <x-form-field label="E-mail" name="email" type="email" :value="old('email', request('email'))" required />
        <x-form-field label="Nova senha" name="password" type="password" autocomplete="new-password" required />
        <x-form-field label="Confirme a senha" name="password_confirmation" type="password" autocomplete="new-password" required />
        <button type="submit" class="editorial-button w-full">Redefinir senha</button>
    </form>
@endsection
