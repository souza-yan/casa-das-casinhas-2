@extends('layouts.guest', ['title' => 'Nova senha — Casa das Casinhas'])

@section('content')
    <h1 class="font-serif text-4xl tracking-[-0.04em]">Defina uma nova senha</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-8 grid gap-5">
        @csrf
        <input type="hidden" name="token" value="{{ request()->route('token') }}">
        <x-form-field label="E-mail" name="email" type="email" :value="old('email', request('email'))" required />
        <x-form-field label="Nova senha" name="password" type="password" autocomplete="new-password" required />
        <x-form-field label="Confirme a senha" name="password_confirmation" type="password" autocomplete="new-password" required />
        <button type="submit" class="h-12 bg-[#4a2a16] px-5 text-sm font-semibold uppercase tracking-[0.12em] text-white">Redefinir senha</button>
    </form>
@endsection
