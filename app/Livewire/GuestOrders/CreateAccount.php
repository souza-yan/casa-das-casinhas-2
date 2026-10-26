<?php

namespace App\Livewire\GuestOrders;

use App\Enums\Role;
use App\Models\Order;
use App\Models\User;
use App\Services\GuestOrderAccessService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Component;
use TallStackUi\Traits\Interactions;

#[Layout('layouts.public')]
class CreateAccount extends Component
{
    use Interactions;

    public Order $order;

    public bool $existingAccount = false;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(Order $order, GuestOrderAccessService $access): void
    {
        abort_unless(
            $order->user_id === null
            && $order->guest_email_verified_at
            && $access->hasSessionAccess($order),
            403,
        );

        $this->order = $order;
        $this->existingAccount = User::where('email', $order->customer_email)->exists();
    }

    public function createAccount(GuestOrderAccessService $access)
    {
        if ($this->existingAccount) {
            return;
        }

        $validated = $this->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = User::create([
            'name' => $this->order->customer_name,
            'email' => $this->order->customer_email,
            'email_verified_at' => now(),
            'password' => Hash::make($validated['password']),
            'role' => Role::CUSTOMER,
            'phone' => $this->order->customer_phone,
            'zip_code' => $this->order->address->zip_code,
            'street' => $this->order->address->street,
            'number' => $this->order->address->number,
            'complement' => $this->order->address->complement,
            'district' => $this->order->address->district,
            'city' => $this->order->address->city,
            'state' => $this->order->address->state,
        ]);

        $this->order->update(['user_id' => $user->id]);
        $access->claimVerifiedOrderFor($user);
        Auth::login($user);
        request()->session()->regenerate();

        $this->toast()->success('Conta criada', 'Agora você pode acompanhar suas encomendas pela sua conta.')->flash()->send();

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.guest-orders.create-account');
    }
}
