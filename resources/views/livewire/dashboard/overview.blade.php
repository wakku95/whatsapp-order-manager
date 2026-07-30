<div class="space-y-6">
    <!-- Welcome Header Card -->
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-6 shadow-sm flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-[#0F172A]">Welcome to {{ $business->name }}</h2>
            <p class="text-sm text-[#64748B] mt-1">Your Phase 1 Tenant Foundation & Onboarding is active.</p>
        </div>
        <div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-[#16A34A]">
                Tenant Isolated: {{ $business->slug }}
            </span>
        </div>
    </div>

    <!-- Overview Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Business Status</p>
            <p class="text-2xl font-bold text-[#16A34A] mt-2 capitalize">{{ $business->status }}</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Currency</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-2">{{ $business->currency }}</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Owner</p>
            <p class="text-2xl font-bold text-[#0F172A] mt-2 truncate">{{ auth()->user()->name }}</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Phase Status</p>
            <p class="text-2xl font-bold text-[#2563EB] mt-2">Phase 1 Complete</p>
        </div>
    </div>

    <!-- Onboarding Success Banner -->
    <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-6">
        <h3 class="text-base font-bold text-[#0F172A] mb-2">Phase 1 Infrastructure Ready</h3>
        <ul class="text-sm text-[#64748B] space-y-2 list-disc list-inside">
            <li>Zero-trust Multi-tenancy enabled via <code class="bg-white px-2 py-0.5 border rounded text-[#16A34A]">TenantScope</code> and <code class="bg-white px-2 py-0.5 border rounded text-[#16A34A]">BelongsToTenant</code>.</li>
            <li>Authentication and business onboarding workflow verified.</li>
            <li>All 11 MySQL schema tables created with strict foreign key constraints & indexes.</li>
        </ul>
    </div>
</div>
