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
<body class="bg-[#F8FAFC] text-[#0F172A] font-sans antialiased">
    <div class="min-h-screen flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-white border-r border-[#E2E8F0] flex flex-col justify-between shrink-0">
            <div>
                <!-- Brand -->
                <div class="h-16 flex items-center px-6 border-b border-[#E2E8F0] space-x-2">
                    <span class="text-[#16A34A] font-bold text-xl flex items-center space-x-2">
                        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.233-1.237a9.96 9.96 0 004.779 1.221h.005c5.505 0 9.988-4.478 9.989-9.985A9.98 9.98 0 0012.012 2zm0 18.25h-.004a8.28 8.28 0 01-4.223-1.157l-.303-.18-3.137.741.758-3.056-.197-.315a8.27 8.27 0 01-1.267-4.298c.001-4.57 3.718-8.286 8.288-8.286 4.568 0 8.284 3.715 8.285 8.286 0 4.57-3.716 8.287-8.285 8.287z"/>
                        </svg>
                        <span>OrderManager</span>
                    </span>
                </div>

                <!-- Business Selector / Name -->
                <div class="px-6 py-4 border-b border-[#E2E8F0] bg-[#F8FAFC]">
                    <p class="text-xs font-semibold text-[#64748B] uppercase tracking-wider">Business</p>
                    <p class="text-sm font-bold text-[#0F172A] truncate">
                        {{ \App\Services\TenantContext::getTenant()?->name ?? 'No Business' }}
                    </p>
                </div>

                <!-- Navigation -->
                <nav class="p-4 space-y-1">
                    <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-[#16A34A] text-white' : 'text-[#64748B] hover:bg-[#F8FAFC] hover:text-[#0F172A]' }}">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 00-1-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 00-1 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-[#64748B] hover:bg-[#F8FAFC] opacity-60 cursor-not-allowed">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        Orders (Phase 2)
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-[#64748B] hover:bg-[#F8FAFC] opacity-60 cursor-not-allowed">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Customers (Phase 2)
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-[#64748B] hover:bg-[#F8FAFC] opacity-60 cursor-not-allowed">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                        Products (Phase 2)
                    </a>
                    <a href="#" class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-[#64748B] hover:bg-[#F8FAFC] opacity-60 cursor-not-allowed">
                        <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                        WhatsApp (Phase 3)
                    </a>
                </nav>
            </div>

            <!-- Footer User Profile & Logout -->
            <div class="p-4 border-t border-[#E2E8F0]">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-[#0F172A]">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-[#64748B] capitalize">{{ auth()->user()->role }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-xs text-[#DC2626] font-medium hover:underline">
                            Logout
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-[#E2E8F0] flex items-center justify-between px-8">
                <h1 class="text-lg font-bold text-[#0F172A]">{{ $title ?? 'Dashboard' }}</h1>
                <div class="flex items-center space-x-4">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        Active Tenant: {{ \App\Services\TenantContext::getTenant()?->slug }}
                    </span>
                </div>
            </header>

            <!-- Main Page Content -->
            <main class="flex-1 p-8 overflow-y-auto">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
</body>
</html>
