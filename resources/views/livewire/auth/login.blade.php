<div>
    <h2 class="text-xl font-bold text-[#0F172A] mb-1 text-center">Welcome back</h2>
    <p class="text-sm text-[#64748B] mb-5 text-center">Log in to manage your WhatsApp orders.</p>

    <form wire:submit.prevent="login" class="space-y-4">
        <div>
            <label for="email" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Email Address</label>
            <input
                type="email"
                id="email"
                wire:model="email"
                autocomplete="email"
                class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]"
                placeholder="owner@store.com"
            >
            @error('email') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div>
            <label for="password" class="block text-xs font-semibold text-[#0F172A] uppercase mb-1">Password</label>
            <input
                type="password"
                id="password"
                wire:model="password"
                autocomplete="current-password"
                class="w-full px-3 py-3 border border-[#E2E8F0] rounded-lg text-sm focus:outline-none focus:border-[#16A34A] focus:ring-1 focus:ring-[#16A34A]"
                placeholder="••••••••"
            >
            @error('password') <span class="text-xs text-[#DC2626] mt-1 block">{{ $message }}</span> @enderror
        </div>

        <div class="flex items-center">
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="checkbox" wire:model="remember" class="rounded border-[#E2E8F0] text-[#16A34A] focus:ring-[#16A34A] w-4 h-4">
                <span class="text-sm text-[#64748B]">Remember me</span>
            </label>
        </div>

        <button
            type="submit"
            class="w-full bg-[#16A34A] hover:bg-[#15803D] text-white font-medium py-3 px-4 rounded-lg text-sm transition-colors"
        >
            Log In
        </button>
    </form>

    <div class="mt-5 text-center text-sm text-[#64748B]">
        Don't have an account?
        <a href="{{ route('register') }}" class="text-[#16A34A] font-semibold hover:underline">Sign up</a>
    </div>
</div>
