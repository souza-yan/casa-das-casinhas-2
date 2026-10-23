<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.public')]
class Profile extends Component
{
    use Interactions;

    public bool $profileModal = false;

    public string $name = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function openEditor(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->resetValidation();
        $this->profileModal = true;
    }

    public function updateProfile(): void
    {
        $user = auth()->user();
        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'current_password' => ['required_with:password', 'nullable', 'current_password:web'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (filled($validated['password'] ?? null)) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();
        $this->profileModal = false;
        $this->reset(['current_password', 'password', 'password_confirmation']);
        $this->toast()->success('Perfil atualizado', 'Suas informações foram salvas.')->send();
    }

    public function cancelEdit(): void
    {
        $this->profileModal = false;
        $this->resetValidation();
        $this->reset(['current_password', 'password', 'password_confirmation']);
    }

    public function render()
    {
        return view('livewire.profile', [
            'ordersCount' => auth()->user()->orders()->count(),
            'latestOrders' => auth()->user()->orders()->latest()->take(3)->get(),
        ]);
    }
}
