<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Careers - Human First</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-surface text-on-surface min-h-screen flex flex-col font-body-md">
    <!-- Header -->
    <header class="sticky top-0 z-50 bg-white/80 dark:bg-surface-container/80 backdrop-blur-md border-b border-surface-container-high shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
        <div class="max-w-container-max-width mx-auto px-gutter h-20 flex items-center justify-between">
            <a href="{{ route('careers') }}" class="flex items-center gap-3 group no-underline">
                <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center group-hover:scale-105 transition-all">
                    <span class="material-symbols-outlined text-primary text-[24px]">diversity_3</span>
                </div>
                <span class="font-headline-lg text-title-md text-primary tracking-tight font-extrabold transition-colors">Human First</span>
            </a>
            
            <nav class="flex items-center gap-8">
                <a href="{{ route('careers') }}" class="text-body-md font-bold text-on-surface-variant hover:text-primary transition-colors no-underline relative py-2 {{ request()->routeIs('careers') ? 'text-primary after:absolute after:bottom-0 after:left-0 after:w-full after:h-0.5 after:bg-primary' : '' }}">
                    Lowongan
                </a>
                <a href="{{ route('mpp.index') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary/10 hover:bg-primary text-primary hover:text-white font-bold rounded-sm text-sm transition-all duration-200 active:scale-95 no-underline">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                    <span>Login/Dashboard</span>
                </a>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-surface-container-low border-t border-surface-container-high py-8 mt-20">
        <div class="max-w-container-max-width mx-auto px-gutter">
            <div class="flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-primary text-[20px]">diversity_3</span>
                    <span class="font-bold text-on-surface">Human First</span>
                </div>
                <div class="flex gap-6 text-sm">
                    <a href="#" class="text-on-surface-variant/70 hover:text-primary transition-colors no-underline">Hubungi Kami</a>
                    <a href="#" class="text-on-surface-variant/70 hover:text-primary transition-colors no-underline">Tentang Kami</a>
                    <a href="#" class="text-on-surface-variant/70 hover:text-primary transition-colors no-underline">Kebijakan Privasi</a>
                </div>
            </div>
            <hr class="my-6 border-surface-container-high">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4 text-xs text-on-surface-variant/50">
                <p>&copy; {{ date('Y') }} Human First. All rights reserved.</p>
                <p>Designed to put people first.</p>
            </div>
        </div>
    </footer>
</body>
</html>
