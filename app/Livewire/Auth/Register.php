<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Register extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';

    protected array $rules = [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:8|confirmed',
    ];

    public function register()
    {
        $this->validate();

        $user = User::create([
            'name' => $this->name,
            'email' => strtolower(trim($this->email)),
            'password' => Hash::make($this->password),
            'role' => 'owner',
            'business_id' => null,
        ]);

        Auth::login($user);

        return redirect()->route('onboarding');
    }

    public function render()
    {
        return view('livewire.auth.register')->layout('layouts.guest', ['title' => 'Register']);
    }
}
