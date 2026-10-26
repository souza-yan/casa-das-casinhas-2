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

    public string $phone = '';

    public string $zip_code = '';

    public string $street = '';

    public string $number = '';

    public ?string $complement = null;

    public string $district = '';

    public string $city = '';

    public string $state = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function openEditor(): void
    {
        $this->name = auth()->user()->name;
        $this->email = auth()->user()->email;
        $this->phone = auth()->user()->phone ?? '';
        $this->zip_code = auth()->user()->zip_code ?? '';
        $this->street = auth()->user()->street ?? '';
        $this->number = auth()->user()->number ?? '';
        $this->complement = auth()->user()->complement;
        $this->district = auth()->user()->district ?? '';
        $this->city = auth()->user()->city ?? '';
        $this->state = auth()->user()->state ?? '';
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
            'phone' => ['nullable', 'string', 'max:30'],
            'zip_code' => ['nullable', 'string', 'max:9', 'required_with:street,number,district,city,state'],
            'street' => ['nullable', 'string', 'max:255', 'required_with:zip_code,number,district,city,state'],
            'number' => ['nullable', 'string', 'max:30', 'required_with:zip_code,street,district,city,state'],
            'complement' => ['nullable', 'string', 'max:255'],
            'district' => ['nullable', 'string', 'max:255', 'required_with:zip_code,street,number,city,state'],
            'city' => ['nullable', 'string', 'max:255', 'required_with:zip_code,street,number,district,state'],
            'state' => ['nullable', 'string', 'size:2', 'required_with:zip_code,street,number,district,city'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'zip_code' => $validated['zip_code'],
            'street' => $validated['street'],
            'number' => $validated['number'],
            'complement' => $validated['complement'],
            'district' => $validated['district'],
            'city' => $validated['city'],
            'state' => filled($validated['state']) ? mb_strtoupper($validated['state']) : null,
        ]);

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
            'latestOrders' => auth()->user()->orders()->latest()->take(3)->get(),
        ]);
    }
}
