@extends('layouts.guest', ['title' => 'Recuperar senha — Casa das Casinhas'])

@section('content')
    <p class="text-sm font-semibold uppercase tracking-[0.2em] text-[#1d4ed8]">Acesso</p>
    <h1 class="mt-3 font-serif text-4xl tracking-[-0.04em]">Recupere sua senha</h1>
    <p class="mt-3 text-sm leading-6 text-[#0f172a]/60">Enviaremos um link de redefinição para seu e-mail.</p>
    @if (session('status'))<div class="mt-6 border border-green-700/20 bg-green-50 p-3 text-sm text-green-800">{{ session('status') }}</div>@endif
    <form method="POST" action="{{ route('password.email') }}" class="mt-8 grid gap-5">
        @csrf
        <x-form-field label="E-mail" name="email" type="email" autocomplete="email" :value="old('email')" required autofocus />
        <button type="submit" class="h-12 bg-[#1d4ed8] px-5 text-sm font-semibold uppercase tracking-[0.12em] text-white">Enviar link</button>
    </form>
@endsection
