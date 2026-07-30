<div>
    <div class="mb-6 text-center">
        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-green-100 text-[#16A34A] mb-3">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m0 0h5m-5 0V11m0 0h5M14 7h-4"/>
            </svg>
        </span>
        <h2 class="text-xl font-bold text-[#0F172A]">Welcome, {{ auth()->user()->name }}!</h2>
        <p class="text-sm text-[#64748B] mt-1">Set up your business profile to get started.</p>
    </div>

    <form wire:submit.prevent="createBusiness" class="space-y-4">
        <div>
            <label for="name" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Business Name</label>
            <input type="text" id="name" wire:model="name" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]" placeholder="e.g. Modern Fashion Apparel">
            @error('name') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="phone" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Business Phone Number</label>
            <input type="text" id="phone" wire:model="phone" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]" placeholder="+1 555 123 4567">
            @error('phone') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="currency" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Default Currency</label>
            <select id="currency" wire:model="currency" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]">
                <option value="USD">USD ($)</option>
                <option value="PKR">PKR (Rs)</option>
                <option value="EUR">EUR (€)</option>
                <option value="GBP">GBP (£)</option>
                <option value="INR">INR (₹)</option>
                <option value="AED">AED (AED)</option>
            </select>
            @error('currency') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="w-full bg-[#16A34A] hover:bg-[#15803D] text-white font-medium py-2.5 px-4 rounded-lg text-sm transition">
            Complete Business Setup & Go to Dashboard
        </button>
    </form>
</div>
