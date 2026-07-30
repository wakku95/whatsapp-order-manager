<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Login extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    protected array $rules = [
        'email' => 'required|email',
        'password' => 'required',
    ];

    public function login()
    {
        $this->validate();

        if (Auth::attempt(['email' => strtolower(trim($this->email)), 'password' => $this->password], $this->remember)) {
            session()->regenerate();

            $user = Auth::user();

            if ($user->business_id === null) {
                return redirect()->route('onboarding');
            }

            return redirect()->intended(route('dashboard'));
        }

        $this->addError('email', 'The provided credentials do not match our records.');
    }

    public function render()
    {
        return view('livewire.auth.login')->layout('layouts.guest', ['title' => 'Log In']);
    }
}
