<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'WhatsApp Order Manager' }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="bg-[#F8FAFC] text-[#0F172A] font-sans antialiased min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md text-center mb-6">
        <div class="inline-flex items-center space-x-2 text-[#16A34A] font-bold text-2xl">
            <svg class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12.012 2c-5.506 0-9.989 4.478-9.99 9.984a9.96 9.96 0 001.333 4.993L2 22l5.233-1.237a9.96 9.96 0 004.779 1.221h.005c5.505 0 9.988-4.478 9.989-9.985A9.98 9.98 0 0012.012 2zm0 18.25h-.004a8.28 8.28 0 01-4.223-1.157l-.303-.18-3.137.741.758-3.056-.197-.315a8.27 8.27 0 01-1.267-4.298c.001-4.57 3.718-8.286 8.288-8.286 4.568 0 8.284 3.715 8.285 8.286 0 4.57-3.716 8.287-8.285 8.287z"/>
            </svg>
            <span>WhatsApp Order Manager</span>
        </div>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="bg-white py-8 px-6 shadow-sm border border-[#E2E8F0] rounded-xl sm:px-10">
            {{ $slot }}
        </div>
    </div>

    @livewireScripts
</body>
</html>
