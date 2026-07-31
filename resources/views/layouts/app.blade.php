<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Dashboard - WhatsApp Order Manager' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] font-sans antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex">

        {{-- ============================================================
             Mobile backdrop overlay — closes sidebar when tapped
        ============================================================ --}}
        <div
            x-show="sidebarOpen"
            x-transition:enter="transition-opacity ease-linear duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-20 bg-black/40 lg:hidden"
            @click="sidebarOpen = false"
            style="display:none"
        ></div>

        {{-- ============================================================
             Sidebar
             - Mobile: fixed, slides in from left via x-show/transform
             - Desktop (lg+): static, always visible
        ============================================================ --}}
        <aside
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-[#E2E8F0] flex flex-col justify-between
                   transform transition-transform duration-200 ease-in-out
                   lg:static lg:translate-x-0 lg:z-auto lg:shrink-0"
        >
            <div class="flex flex-col min-h-0 flex-1 overflow-y-auto">

                {{-- Brand --}}
                <div class="h-16 flex items-center justify-between px-5 border-b border-[#E2E8F0] shrink-0">
                    <span class="text-[#16A34A] font-bold text-lg flex items-center space-x-2">
                        <svg class="w-6 h-6 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.233-1.237a9.96 9.96 0 004.779 1.221h.005c5.505 0 9.988-4.478 9.989-9.985A9.98 9.98 0 0012.012 2zm0 18.25h-.004a8.28 8.28 0 01-4.223-1.157l-.303-.18-3.137.741.758-3.056-.197-.315a8.27 8.27 0 01-1.267-4.298c.001-4.57 3.718-8.286 8.288-8.286 4.568 0 8.284 3.715 8.285 8.286 0 4.57-3.716 8.287-8.285 8.287z"/>
                        </svg>
                        <span>OrderManager</span>
                    </span>
                    {{-- Close button (mobile only) --}}
                    <button
                        @click="sidebarOpen = false"
                        class="lg:hidden p-1 rounded-md text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC]"
                        aria-label="Close navigation"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                {{-- Business name --}}
                <div class="px-5 py-3 border-b border-[#E2E8F0] bg-[#F8FAFC] shrink-0">
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Business</p>
                    <p class="text-sm font-bold text-[#0F172A] truncate mt-0.5">
                        {{ \App\Services\TenantContext::getTenant()?->name ?? 'No Business' }}
                    </p>
                </div>

                {{-- Navigation --}}
                <nav class="p-3 space-y-0.5 flex-1">
                    <a href="{{ route('dashboard') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors
                              {{ request()->routeIs('dashboard') ? 'bg-[#16A34A] text-white' : 'text-[#64748B] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    {{-- Future nav items — disabled --}}
                    @foreach ([
                        ['icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', 'label' => 'Orders', 'phase' => '2'],
                        ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Customers', 'phase' => '2'],
                        ['icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'label' => 'Products', 'phase' => '2'],
                        ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'WhatsApp', 'phase' => '3'],
                    ] as $item)
                    <span class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-[#64748B] opacity-50 cursor-not-allowed select-none">
                        <svg class="w-5 h-5 mr-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="truncate">{{ $item['label'] }}</span>
                        <span class="ml-auto text-[10px] bg-[#E2E8F0] text-[#64748B] rounded px-1 shrink-0">P{{ $item['phase'] }}</span>
                    </span>
                    @endforeach
                </nav>
            </div>

            {{-- User profile + logout --}}
            <div class="p-4 border-t border-[#E2E8F0] shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-[#0F172A] truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#64748B] capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="text-xs text-[#DC2626] font-medium hover:underline whitespace-nowrap">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- ============================================================
             Main content area
        ============================================================ --}}
        <div class="flex-1 flex flex-col min-w-0">

            {{-- Topbar --}}
            <header class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-4 sm:px-6 shrink-0">

                {{-- Hamburger (mobile only) + Page title --}}
                <div class="flex items-center gap-3 min-w-0">
                    <button
                        @click="sidebarOpen = true"
                        class="lg:hidden p-2 -ml-1 rounded-md text-[#64748B] hover:text-[#0F172A] hover:bg-[#F8FAFC]"
                        aria-label="Open navigation"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                    <h1 class="text-base sm:text-lg font-bold text-[#0F172A] truncate">{{ $title ?? 'Dashboard' }}</h1>
                </div>

                {{-- Tenant badge — hidden on very small screens to prevent overflow --}}
                <div class="hidden sm:flex items-center shrink-0 ml-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 whitespace-nowrap">
                        {{ \App\Services\TenantContext::getTenant()?->slug }}
                    </span>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
