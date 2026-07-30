<?php

namespace App\Livewire\Onboarding;

use App\Services\BusinessService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class CreateBusiness extends Component
{
    public string $name = '';
    public string $phone = '';
    public string $currency = 'USD';

    protected array $rules = [
        'name' => 'required|string|min:3|max:255',
        'phone' => 'nullable|string|max:50',
        'currency' => 'required|string|size:3',
    ];

    public function createBusiness(BusinessService $businessService)
    {
        $this->validate();

        $user = Auth::user();

        if ($user->business_id !== null) {
            return redirect()->route('dashboard');
        }

        $businessService->createBusinessForUser($user, [
            'name' => trim($this->name),
            'phone' => trim($this->phone),
            'currency' => strtoupper($this->currency),
        ]);

        return redirect()->route('dashboard');
    }

    public function render()
    {
        return view('livewire.onboarding.create-business')->layout('layouts.guest', ['title' => 'Set Up Business']);
    }
}
