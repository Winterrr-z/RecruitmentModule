<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Applicant Portal - Human First</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface min-h-screen flex font-body-md">

    <!-- Left Sidebar -->
    <aside class="w-64 min-h-screen bg-surface border-r border-outline-variant flex flex-col fixed left-0 top-0 z-50">
        <!-- Logo and Branding -->
        <div class="p-gutter mb-8">
            <a class="flex items-center gap-3 active:scale-95 transition-transform group no-underline" href="/">
                <img alt="Human First Logo" class="h-8 w-auto" src="https://lh3.googleusercontent.com/aida/ADBb0ugrpLiJy26Io_mLDAQUQEnf730xr_rABFyyY9ICKsaSPA5_GH1W8-QK1fD0RYxjtYsrzgxiLqamtB5Cf7PHSU2VVk-26EclV5EbaORiivGaTvJaDE89sPUodINDL5bX3qnXtwACKPwFJXWkJbiN7pI5K01QjstbO5c1JVQz0Jm3F0f4WyfPpHri7TSjpW7g0ybWdcDeq5FdFZNOE0vVonSrcR27cT44HXIFCkDQuHadqH2jtq-eCa8vl_BA"/>
                <span class="font-display text-lg font-bold text-primary tracking-tight">Human First</span>
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
            <a href="{{ route('candidate.logout') }}" 
               onclick="event.preventDefault(); document.getElementById('logout-form').submit();" 
               class="flex items-center gap-3 px-4 py-3 text-on-surface-variant hover:bg-error-container/10 hover:text-error transition-colors rounded-lg font-medium no-underline">
                <span class="material-symbols-outlined text-error">logout</span>
                <span>Keluar</span>
            </a>
            <form id="logout-form" action="{{ route('candidate.logout') }}" method="POST" class="hidden">
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
    <main class="flex-grow ml-64 min-h-screen flex flex-col">
        <!-- Dynamic Content Section -->
        <section class="flex-grow px-gutter py-section-padding-desktop max-w-7xl mx-auto w-full">
            {{ $slot }}
        </section>

        <!-- Footer -->
        <footer class="bg-surface-container-low dark:bg-inverse-surface border-t border-surface-container-high py-8 mt-12 w-full">
            <div class="flex flex-col md:flex-row justify-between items-center px-gutter max-w-7xl mx-auto gap-6 text-sm text-on-surface-variant/70">
                <div class="font-bold text-primary opacity-80">
                    Human First
                </div>
                <div class="flex flex-wrap justify-center gap-6">
                    <a class="text-on-surface-variant dark:text-outline-variant hover:text-primary no-underline transition-colors" href="#">Privacy Policy</a>
                    <a class="text-on-surface-variant dark:text-outline-variant hover:text-primary no-underline transition-colors" href="#">Terms of Service</a>
                    <a class="text-on-surface-variant dark:text-outline-variant hover:text-primary no-underline transition-colors" href="#">Cookie Settings</a>
                </div>
                <div>
                    &copy; {{ date('Y') }} Human First. All rights reserved.
                </div>
            </div>
        </footer>
    </main>

</body>
</html>
