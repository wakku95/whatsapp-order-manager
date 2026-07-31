<div class="space-y-4 sm:space-y-6">

    {{-- Welcome Header Card --}}
    <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 sm:p-6 shadow-sm">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="min-w-0">
                <h2 class="text-lg sm:text-xl font-bold text-[#0F172A] truncate">Welcome to {{ $business->name }}</h2>
                <p class="text-sm text-[#64748B] mt-1">Your Phase 1 Tenant Foundation &amp; Onboarding is active.</p>
            </div>
            <div class="shrink-0">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-[#16A34A]">
                    Tenant: {{ $business->slug }}
                </span>
            </div>
        </div>
    </div>

    {{-- Overview Stats Grid: 2 cols on mobile, 4 on md+ --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6">
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Status</p>
            <p class="text-xl sm:text-2xl font-bold text-[#16A34A] mt-2 capitalize">{{ $business->status }}</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Currency</p>
            <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-2">{{ $business->currency }}</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Owner</p>
            <p class="text-xl sm:text-2xl font-bold text-[#0F172A] mt-2 truncate">{{ auth()->user()->name }}</p>
        </div>
        <div class="bg-white border border-[#E2E8F0] rounded-xl p-4 sm:p-5 shadow-sm">
            <p class="text-xs font-semibold text-[#64748B] uppercase">Phase</p>
            <p class="text-lg sm:text-2xl font-bold text-[#2563EB] mt-2 leading-tight">Phase 1<br class="sm:hidden"> Done</p>
        </div>
    </div>

    {{-- Onboarding Success Banner --}}
    <div class="bg-[#F8FAFC] border border-[#E2E8F0] rounded-xl p-4 sm:p-6">
        <h3 class="text-sm sm:text-base font-bold text-[#0F172A] mb-2">Phase 1 Infrastructure Ready</h3>
        <ul class="text-sm text-[#64748B] space-y-2 list-disc list-inside">
            <li>Zero-trust Multi-tenancy via
                <code class="bg-white px-1.5 py-0.5 border rounded text-[#16A34A] text-xs">TenantScope</code>
                and
                <code class="bg-white px-1.5 py-0.5 border rounded text-[#16A34A] text-xs">BelongsToTenant</code>.
            </li>
            <li>Authentication &amp; business onboarding verified.</li>
            <li>All 11 MySQL schema tables with FK constraints &amp; indexes.</li>
        </ul>
    </div>

</div>
