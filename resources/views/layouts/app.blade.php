@php
    $requestPath = request()->path();

    if (request()->routeIs('mpp.*') || str_starts_with($requestPath, 'mpp')) {
        $headerTitle = 'Manpower Planning';
    } elseif (request()->routeIs('rr.*') || str_starts_with($requestPath, 'rr')) {
        $headerTitle = 'Recruitment Request';
    } elseif (request()->routeIs('ats.*') || str_starts_with($requestPath, 'ats')) {
        $headerTitle = 'Applicant Tracking System';
    } elseif (request()->routeIs('dashboard') || str_starts_with($requestPath, 'dashboard')) {
        $headerTitle = 'Dashboard';
    } elseif (request()->routeIs('profile.*') || str_starts_with($requestPath, 'profile')) {
        $headerTitle = 'Profile';
    } elseif (request()->routeIs('settings.*') || str_starts_with($requestPath, 'settings')) {
        $headerTitle = 'Settings';
    } else {
        $headerTitle = 'Dashboard';
    }
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface font-body-md min-h-screen flex flex-col">
    <!-- Block Overlay for mobile -->
    <div class="lg:hidden fixed inset-0 z-50 flex flex-col items-center justify-center bg-surface p-8 text-center text-on-surface">
        <div class="w-20 h-20 rounded-full bg-primary/10 flex items-center justify-center mb-6 shadow-md">
            <span class="material-symbols-outlined text-primary text-[40px]">desktop_mac</span>
        </div>
        <h2 class="font-headline-lg text-headline-lg text-on-surface font-extrabold mb-3">Akses Khusus Desktop</h2>
        <p class="font-body-md text-body-md text-on-surface-variant max-w-sm">
            Maaf, halaman dashboard dan manajemen HR hanya dapat diakses melalui komputer atau desktop web untuk kenyamanan dan keamanan optimal.
        </p>
    </div>

    <!-- SideNavBar -->
<aside class="hidden lg:flex fixed left-0 top-0 flex-col py-8 gap-4 bg-surface-container-low/50 backdrop-blur-xl h-full w-72 rounded-r-sm z-40">
    <div class="flex items-center gap-3 px-8 mb-8">
        <img alt="{{ config('company.name') }} Company Logo" class="h-12 w-auto" src="{{ config('company.logo') }}">
        <span class="font-headline-lg text-headline-lg text-primary tracking-tight">ATT Group</span>
    </div>
    <nav class="flex flex-col gap-2 px-4">
        <a class="{{ request()->routeIs('dashboard') ? 'bg-primary-container text-on-primary-container font-semibold scale-102' : 'text-on-surface-variant hover:text-primary hover:bg-primary/10' }} rounded-md px-6 py-3 flex items-center gap-4 transition-all" href="{{ route('dashboard') }}">
            <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
            <span class="font-body-md text-body-md">Dashboard</span>
        </a>
        <a class="{{ request()->routeIs('mpp.*') ? 'bg-primary-container text-on-primary-container font-semibold scale-102' : 'text-on-surface-variant hover:text-primary hover:bg-primary/10' }} rounded-md px-6 py-3 flex items-center gap-4 transition-all" href="{{ route('mpp.index') }}">
            <span class="material-symbols-outlined" data-icon="group_add">group_add</span>
            <span class="font-body-md text-body-md">Manpower Planning</span>
        </a>
        <a href="{{ route('rr.index') }}" class="{{ request()->routeIs('rr.*') ? 'bg-primary-container text-on-primary-container font-semibold scale-102' : 'text-on-surface-variant hover:text-primary hover:bg-primary/10' }} rounded-md px-6 py-3 flex items-center gap-4 transition-all">
            <span class="material-symbols-outlined">description</span>
            <span class="font-body-md text-body-md">Recruitment Request</span>
        </a>
        <!-- ATS Dropdown Menu -->
        <div x-data="{ open: {{ (request()->routeIs('ats.*') || str_starts_with($requestPath, 'ats')) ? 'true' : 'false' }} }" class="flex flex-col">
            <button @click="open = !open" 
                    class="{{ (request()->routeIs('ats.*') || str_starts_with($requestPath, 'ats')) ? 'bg-primary-container text-on-primary-container font-semibold scale-102' : 'text-on-surface-variant hover:text-primary hover:bg-primary/10' }} rounded-md px-6 py-3 flex items-center justify-between transition-all w-full text-left">
                <div class="flex items-center gap-4">
                    <span class="material-symbols-outlined" data-icon="person_search">person_search</span>
                    <span class="font-body-md text-body-md">ATS</span>
                </div>
                <span class="material-symbols-outlined transition-transform duration-200" :class="open ? 'rotate-180' : ''">keyboard_arrow_down</span>
            </button>
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-150"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-100"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform -translate-y-2"
                 class="pl-10 flex flex-col gap-1 mt-1">
                <a href="{{ route('ats.dashboard') }}" 
                   class="{{ request()->routeIs('ats.dashboard') ? 'text-primary font-bold bg-primary/10' : 'text-on-surface-variant hover:text-primary hover:bg-primary/5' }} px-4 py-2 flex items-center gap-3 rounded-md transition-all">
                    <span class="material-symbols-outlined text-[18px]">account_tree</span>
                    <span class="font-body-md text-sm">Pipeline</span>
                </a>
                <a href="{{ route('ats.stages') }}" 
                   class="{{ request()->routeIs('ats.stages') ? 'text-primary font-bold bg-primary/10' : 'text-on-surface-variant hover:text-primary hover:bg-primary/5' }} px-4 py-2 flex items-center gap-3 rounded-md transition-all">
                    <span class="material-symbols-outlined text-[18px]">settings</span>
                    <span class="font-body-md text-sm">Config Stage</span>
                </a>
            </div>
        </div>
        <div class="mt-auto pt-8 flex flex-col gap-1">
            <a class="text-on-surface-variant hover:text-primary px-6 py-3 flex items-center gap-4 hover:bg-primary/10 rounded-md transition-all" href="settings">
                <span class="material-symbols-outlined" data-icon="settings">settings</span>
                <span class="font-body-md text-body-md">Settings</span>
            </a>
            <a href="{{ route('candidate.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-on-surface-variant hover:text-error px-6 py-3 flex items-center gap-4 hover:bg-error-container/10 rounded-md transition-all">
                <span class="material-symbols-outlined">logout</span>
                <span class="font-body-md text-body-md">Keluar</span>
            </a>
        </div>
    </nav>
    <a href="{{ route('hr.profile') }}" class="group transition-all hover:bg-primary/10 mt-auto px-6 py-4 mx-2 rounded-md flex items-center gap-4 cursor-pointer">
        <div class="w-12 h-12 rounded-full overflow-hidden ring-2 ring-transparent group-hover:ring-primary/30 transition-all bg-primary/10 flex items-center justify-center">
            @if(auth()->check() && auth()->user()->profile_photo_path)
                <img alt="{{ auth()->user()->name }}" class="w-full h-full object-cover" src="{{ Storage::url(auth()->user()->profile_photo_path) }}">
            @else
                <span class="material-symbols-outlined text-primary text-[24px]">person</span>
            @endif
        </div>
        <div>
            <p class="font-title-md text-sm font-bold text-on-surface group-hover:text-primary transition-colors">{{ auth()->check() ? auth()->user()->name : 'Admin Utama' }}</p>
            <p class="font-label-sm text-xs text-on-surface-variant uppercase tracking-widest">{{ auth()->check() ? (auth()->user()->job_title ?? 'HR Staff') : 'HR Manager' }}</p>
        </div>
    </a>
</aside>
    <main class="hidden lg:flex lg:flex-col min-h-screen ml-72 flex-grow">
    <!-- Header -->
    <header class="sticky top-4 z-50 flex justify-between items-center px-8 py-4 mx-auto bg-surface/80 dark:bg-surface-container/80 backdrop-blur-md rounded-md mt-4 w-[calc(100%-2rem)] shadow-[0_20px_40px_rgba(107,56,212,0.06)]">
        <div class="flex items-center gap-4">
            <span class="font-headline-lg text-headline-lg text-primary tracking-tight">{{ $headerTitle }}</span>
        </div>
        <div class="flex items-center gap-6">
            <livewire:global-search />
            <button class="bg-primary/10 text-primary w-12 h-12 flex items-center justify-center rounded-md hover:bg-primary hover:text-white transition-all active:scale-95"><span class="material-symbols-outlined">notifications</span></button>
        </div>
    </header>
    <!-- Akhir Header -->
    <div class="p-gutter w-full pb-8">
        {{ $slot }}
    </div>
</main>

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('candidate.logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- Global Flash Message Toast -->
    @if(session()->has('message') || session()->has('success') || session()->has('error'))
    <div id="global-toast" class="fixed top-6 right-6 z-[9999] flex items-center w-full max-w-sm p-4 rounded-lg shadow-lg border transition-all duration-500 transform translate-y-0 opacity-100 {{ session()->has('error') ? 'bg-red-50 text-red-800 border-red-100 dark:bg-red-900/30 dark:text-red-300 dark:border-red-800' : 'bg-green-50 text-green-800 border-green-100 dark:bg-green-900/30 dark:text-green-300 dark:border-green-800' }}" role="alert">
        <div class="inline-flex items-center justify-center flex-shrink-0 w-8 h-8 rounded-lg {{ session()->has('error') ? 'text-red-500 bg-red-100 dark:bg-red-800/40 dark:text-red-300' : 'text-green-500 bg-green-100 dark:bg-green-800/40 dark:text-green-300' }}">
            <span class="material-symbols-outlined text-[20px]">{{ session()->has('error') ? 'warning' : 'check_circle' }}</span>
        </div>
        <div class="ms-3 text-sm font-semibold">{{ session('message') ?? session('success') ?? session('error') }}</div>
        <button type="button" onclick="document.getElementById('global-toast').remove()" class="ms-auto -mx-1.5 -my-1.5 rounded-lg p-1.5 inline-flex items-center justify-center h-8 w-8 transition-colors {{ session()->has('error') ? 'text-red-500 hover:bg-red-200/50 dark:hover:bg-red-800/20' : 'text-green-500 hover:bg-green-200/50 dark:hover:bg-green-800/20' }}" aria-label="Close">
            <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
    </div>
    <script>
        setTimeout(function() {
            var toast = document.getElementById('global-toast');
            if (toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-1rem)';
                setTimeout(function() {
                    toast.remove();
                }, 500);
            }
        }, 5000);
    </script>
    @endif

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