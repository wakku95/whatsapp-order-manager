<div>
    <h2 class="text-xl font-bold text-[#0F172A] mb-2 text-center">Create your account</h2>
    <p class="text-sm text-[#64748B] mb-6 text-center">Start managing your WhatsApp orders in minutes.</p>

    <form wire:submit.prevent="register" class="space-y-4">
        <div>
            <label for="name" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Full Name</label>
            <input type="text" id="name" wire:model="name" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]" placeholder="John Doe">
            @error('name') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Email Address</label>
            <input type="email" id="email" wire:model="email" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]" placeholder="owner@store.com">
            @error('email') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Password</label>
            <input type="password" id="password" wire:model="password" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]" placeholder="••••••••">
            @error('password') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Confirm Password</label>
            <input type="password" id="password_confirmation" wire:model="password_confirmation" class="w-full px-3 py-2 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]" placeholder="••••••••">
        </div>

        <button type="submit" class="w-full bg-[#16A34A] hover:bg-[#15803D] text-white font-medium py-2.5 px-4 rounded-lg text-sm transition">
            Create Account & Continue
        </button>
    </form>

    <div class="mt-6 text-center text-xs text-[#64748B]">
        Already have an account? <a href="{{ route('login') }}" class="text-[#16A34A] font-semibold hover:underline">Log in</a>
    </div>
</div>
