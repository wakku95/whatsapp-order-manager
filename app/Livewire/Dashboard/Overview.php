<?php

namespace App\Livewire\Dashboard;

use App\Services\TenantContext;
use Livewire\Component;

class Overview extends Component
{
    public function render()
    {
        $business = TenantContext::getTenant();

        return view('livewire.dashboard.overview', [
            'business' => $business,
        ])->layout('layouts.app', ['title' => 'Dashboard Overview']);
    }
}
