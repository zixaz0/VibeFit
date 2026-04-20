<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — VibeFit</title>

    {{-- Tailwind CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50:  '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        },
                        accent: '#f97316',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                        display: ['"Sora"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .ring-brand { --tw-ring-color: #22c55e; }
        .progress-bar { transition: width 0.8s cubic-bezier(0.34, 1.56, 0.64, 1); }
        .upload-zone { transition: all 0.2s ease; }
        .upload-zone.dragover { background: #f0fdf4; border-color: #22c55e; transform: scale(1.01); }
        .card-hover { transition: transform 0.2s, box-shadow 0.2s; }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1); }
        @keyframes pulse-green { 0%,100% { opacity:1; } 50% { opacity:.5; } }
        .pulse-green { animation: pulse-green 2s infinite; }
        @keyframes fadeIn { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
        .fade-in { animation: fadeIn 0.4s ease forwards; }
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 1s linear infinite; }

        /* Mobile menu slide animation */
        #mobile-menu {
            transition: max-height 0.3s ease, opacity 0.3s ease;
            max-height: 0;
            opacity: 0;
            overflow: hidden;
        }
        #mobile-menu.open {
            max-height: 400px;
            opacity: 1;
        }

        /* Hamburger icon transition */
        #hamburger-icon,
        #close-icon {
            transition: opacity 0.2s ease;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full bg-gray-50 text-gray-800">

{{-- NAVBAR --}}
@auth
<nav class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm">
    <div class="max-w-6xl mx-auto px-4 sm:px-6">
        <div class="flex items-center justify-between h-16">

            {{-- Logo --}}
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                <div class="w-8 h-8 bg-brand-500 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <span class="font-display font-bold text-gray-900 text-lg tracking-tight">VibeFit</span>
            </a>

            {{-- Desktop Nav Links (hidden on mobile) --}}
            <div class="hidden sm:flex items-center gap-1">
                <a href="{{ route('dashboard') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-100' }} transition">
                    Dashboard
                </a>
                <a href="{{ route('food.history') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('food.history') ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-100' }} transition">
                    Riwayat
                </a>
                <a href="{{ route('profile') }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium {{ request()->routeIs('profile') ? 'bg-brand-50 text-brand-700' : 'text-gray-600 hover:bg-gray-100' }} transition">
                    Profil
                </a>
                <form method="POST" action="{{ route('logout') }}" class="ml-2">
                    @csrf
                    <button type="submit"
                            class="px-4 py-2 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition">
                        Keluar
                    </button>
                </form>
            </div>

            {{-- Hamburger Button (visible on mobile only) --}}
            <button id="hamburger-btn"
                    class="sm:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg text-gray-600 hover:bg-gray-100 transition"
                    aria-label="Buka menu"
                    aria-expanded="false"
                    aria-controls="mobile-menu">
                {{-- Hamburger icon --}}
                <svg id="hamburger-icon" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
                {{-- Close icon (hidden by default) --}}
                <svg id="close-icon" class="w-5 h-5 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- Mobile Menu (hidden by default) --}}
    <div id="mobile-menu" class="sm:hidden border-t border-gray-100">
        <div class="px-4 py-3 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-brand-50 text-brand-700' : 'text-gray-700 hover:bg-gray-100' }} transition">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>
            <a href="{{ route('food.history') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('food.history') ? 'bg-brand-50 text-brand-700' : 'text-gray-700 hover:bg-gray-100' }} transition">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Riwayat
            </a>
            <a href="{{ route('profile') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->routeIs('profile') ? 'bg-brand-50 text-brand-700' : 'text-gray-700 hover:bg-gray-100' }} transition">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                Profil
            </a>
            <div class="pt-1 border-t border-gray-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-3 w-full px-3 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50 transition">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>

<script>
    const btn = document.getElementById('hamburger-btn');
    const menu = document.getElementById('mobile-menu');
    const hamIcon = document.getElementById('hamburger-icon');
    const closeIcon = document.getElementById('close-icon');

    btn.addEventListener('click', () => {
        const isOpen = menu.classList.toggle('open');
        hamIcon.classList.toggle('hidden', isOpen);
        closeIcon.classList.toggle('hidden', !isOpen);
        btn.setAttribute('aria-expanded', isOpen);
    });

    // Close menu when a link is clicked
    menu.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            menu.classList.remove('open');
            hamIcon.classList.remove('hidden');
            closeIcon.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
        });
    });
</script>
@endauth

{{-- FLASH MESSAGES --}}
@if(session('success'))
<div id="flash-success"
     class="fixed top-20 right-4 z-50 bg-brand-500 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 fade-in">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    <span class="text-sm font-medium">{{ session('success') }}</span>
</div>
<script>setTimeout(() => document.getElementById('flash-success')?.remove(), 4000)</script>
@endif

@if(session('error'))
<div id="flash-error"
     class="fixed top-20 right-4 z-50 bg-red-500 text-white px-5 py-3 rounded-xl shadow-lg flex items-center gap-3 fade-in">
    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
    </svg>
    <span class="text-sm font-medium">{{ session('error') }}</span>
</div>
<script>setTimeout(() => document.getElementById('flash-error')?.remove(), 4000)</script>
@endif

{{-- MAIN CONTENT --}}
<main class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
    @yield('content')
</main>

@stack('scripts')
</body>
</html>