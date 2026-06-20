<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Portal - {{ config('company.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800&family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface min-h-screen flex font-body-md">

    <!-- Left Sidebar (Flowbite off-canvas drawer on mobile) -->
    <aside id="applicant-sidebar" class="fixed top-0 left-0 z-40 w-64 h-screen transition-transform -translate-x-full lg:translate-x-0 bg-surface border-r border-outline-variant flex flex-col">
        <!-- Logo and Branding -->
        <div class="p-gutter mb-8">
            <a class="flex items-center gap-3 active:scale-95 transition-transform group no-underline" href="/">
                <img alt="{{ config('company.name') }} Logo" class="h-8 w-auto" src="{{ asset(config('company.logo')) }}"/>
                <span class="font-display text-lg font-bold text-primary tracking-tight">{{ config('company.name') }}</span>
            </a>
        </div>

        <!-- Navigation Links -->
        <nav class="flex flex-col gap-2 px-4 flex-grow">
            <a href="{{ route('candidate.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg no-underline transition-colors {{ request()->routeIs('candidate.dashboard') ? 'sidebar-active font-semibold' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                <span class="material-symbols-outlined">dashboard</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('candidate.jobs') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg no-underline transition-colors {{ request()->routeIs('candidate.jobs') ? 'sidebar-active font-semibold' : 'text-on-surface-variant hover:bg-surface-container hover:text-primary' }}">
                <span class="material-symbols-outlined">work</span>
                <span>Jobs</span>
            </a>

            <!-- Logout Link -->
            <a href="{{ route('logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="flex items-center gap-3 px-4 py-3 text-on-surface-variant hover:bg-error-container/10 hover:text-error transition-colors rounded-lg font-medium no-underline">
                <span class="material-symbols-outlined text-error">logout</span>
                <span>Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                @csrf
            </form>
        </nav>

        <!-- User Profile Card -->
        <div class="p-4 border-t border-outline-variant bg-surface-container-low/30">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 border border-primary/10">
                    <img alt="User profile avatar" class="w-full h-full object-cover" 
                         src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=6b38d4&color=fff' }}"/>
                </div>
                <div class="flex flex-col overflow-hidden">
                    <span class="text-sm font-bold truncate text-on-surface">{{ Auth::user()->name }}</span>
                    <span class="text-xs text-on-surface-variant truncate">{{ Auth::user()->email }}</span>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-grow lg:ml-64 min-h-screen flex flex-col">
        <!-- Mobile Header (Hidden on Desktop) -->
        <header class="lg:hidden flex items-center justify-between px-6 py-4 bg-white border-b border-surface-container-high sticky top-0 z-30 shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
            <div class="flex items-center gap-3">
                <button data-drawer-target="applicant-sidebar" data-drawer-toggle="applicant-sidebar" aria-controls="applicant-sidebar" type="button" class="p-2 -ml-2 text-on-surface-variant hover:text-primary transition-colors">
                    <span class="material-symbols-outlined text-[24px]">menu</span>
                </button>
                <span class="font-display text-base font-bold text-primary tracking-tight">Portal Pelamar</span>
            </div>
            <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 border border-primary/10">
                <img alt="User profile avatar" class="w-full h-full object-cover" 
                     src="{{ Auth::user()->avatar ?? 'https://ui-avatars.com/api/?name='.urlencode(Auth::user()->name).'&background=6b38d4&color=fff' }}"/>
            </div>
        </header>

        <!-- Dynamic Content Section -->
        <section class="flex-grow px-gutter pt-8 pb-section-padding-desktop w-full">
            {{ $slot }}
        </section>

        <!-- Footer -->
        <footer class="bg-surface-container-low dark:bg-inverse-surface border-t border-surface-container-high py-8 mt-12 w-full">
            <div class="flex flex-col md:flex-row justify-between items-center px-gutter w-full gap-6 text-sm text-on-surface-variant/70">
                <div class="font-bold text-primary opacity-80">
                    {{ config('company.name') }}
                </div>
                <div class="flex flex-wrap justify-center gap-6">
                    <a class="text-on-surface-variant dark:text-outline-variant hover:text-primary no-underline transition-colors" href="#">Privacy Policy</a>
                    <a class="text-on-surface-variant dark:text-outline-variant hover:text-primary no-underline transition-colors" href="#">Terms of Service</a>
                    <a class="text-on-surface-variant dark:text-outline-variant hover:text-primary no-underline transition-colors" href="#">Cookie Settings</a>
                </div>
                <div>
                    &copy; {{ date('Y') }} {{ config('company.name') }}. All rights reserved.
                </div>
            </div>
        </footer>
    </main>

    <x-toast-alert />

    <!-- Idle Timeout Script (30 Menit) -->
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let idleTimer;
            const idleTimeout = 30 * 60 * 1000; // 30 menit

            function resetTimer() {
                clearTimeout(idleTimer);
                idleTimer = setTimeout(logoutUser, idleTimeout);
            }

            function logoutUser() {
                var form = document.getElementById('logout-form');
                if (form) form.submit();
            }

            // Reset timer on activity
            const events = ['mousemove', 'keydown', 'click', 'scroll', 'touchstart'];
            events.forEach(function (event) {
                document.addEventListener(event, resetTimer, true);
            });

            resetTimer();
        });
    </script>
    @endauth
</body>
</html>
