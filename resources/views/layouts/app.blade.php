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
<body>
    <!-- SideNavBar -->
<aside class="fixed left-0 top-0 h-screen flex flex-col py-8 gap-4 bg-surface-container-low/50 backdrop-blur-xl h-full w-72 rounded-r-sm z-40">
    <div class="flex items-center gap-3 mb-2 px-8 mb-8">
        <img alt="Human First Company Logo" class="h-12 w-auto" src="https://lh3.googleusercontent.com/aida-public/AB6AXuArvIhduHmMzrhk2FNidLA8cUpVK9DgP2amH6bhd_Oj219BIP1iwGUvq8yJLBIXMdB7By_NaH2-1weYeSLF04ZdRDcMDQ5p4PWQjLH1AbFHbS52Hguy5K8L4cJptKFqS9-Pdp7u1k4rCWhrxquGspZDgILG0MxUEk8tIDNgK2Rn7p9v_g5oF9tSNcqmt5VC0qo-QgP76kcY-oTg2FssGWorMJuHsk2hVaKMlZB9OPvxY0DDEE_Rw4HndUeJZ4mgcBDNfGIatlCYTSkU">
        <span class="font-headline-lg text-headline-lg text-primary tracking-tight">ATT Group</span>
    </div>
    <nav class="flex flex-col gap-2 px-4">
        <a class="text-on-surface-variant hover:text-primary px-6 py-3 flex items-center gap-4 hover:bg-primary/10 rounded-md transition-all" href="#">
            <span class="material-symbols-outlined" data-icon="dashboard">dashboard</span>
            <span class="font-body-md text-body-md">Dashboard</span>
        </a>
        <a class="bg-primary-container text-on-primary-container rounded-md px-6 py-3 font-semibold flex items-center gap-4 transition-all scale-102" href="#">
            <span class="material-symbols-outlined" data-icon="group_add">group_add</span>
            <span class="font-body-md text-body-md">Manpower Planning</span>
        </a>
        <a class="text-on-surface-variant hover:text-primary px-6 py-3 flex items-center gap-4 hover:bg-primary/10 rounded-md transition-all" href="#">
            <span class="material-symbols-outlined" data-icon="description">description</span>
            <span class="font-body-md text-body-md">Recruitment Request</span>
        </a>
        <a class="text-on-surface-variant hover:text-primary px-6 py-3 flex items-center gap-4 hover:bg-primary/10 rounded-md transition-all" href="#">
            <span class="material-symbols-outlined" data-icon="person_search">person_search</span>
            <span class="font-body-md text-body-md">ATS</span>
        </a>
        <div class="mt-auto pt-8">
        <a class="text-on-surface-variant hover:text-primary px-6 py-3 flex items-center gap-4 hover:bg-primary/10 rounded-md transition-all" href="#">
            <span class="material-symbols-outlined" data-icon="settings">settings</span>
            <span class="font-body-md text-body-md">Settings</span>
        </a>
    </div>
    </nav>
    <a href="#" class="group transition-all hover:bg-primary/10 mt-auto px-6 py-4 mx-2 rounded-md flex items-center gap-4 cursor-pointer">
        <img alt="HR Manager Avatar" class="w-12 h-12 rounded-full object-cover ring-2 ring-transparent group-hover:ring-primary/30 transition-all" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAtrw3TdnlyRJ0xaTgf9Qw1SGrevR9PkjyzpDw6BuH5NxfOdfkT6lPMG5Z8xOH_8RLHJFv9tEKBsSXIeeVyxuitfL1L7pP-bQpRt_IyVOurQEltdXmaoJq1ps3SorLaFX4AiRc-flieEevRDiLn6l5yK6G5aaxxSJw1vkwdgc1FZ5q3NwiyqOE0AHkamVHUjgTHHV32NjWyuy8wi5pBoFcueKt72iB3QBolcesRDXwTX-WNpaQttAb7hdt5FIdWaupTU7_bJW1tb4_h">
        <div>
            <p class="font-title-md text-sm font-bold text-on-surface group-hover:text-primary transition-colors">Admin Utama</p>
            <p class="font-label-sm text-xs text-on-surface-variant uppercase tracking-widest">HR Manager</p>
        </div>
    </a>
</aside>
    <main class="min-h-screen ml-72">
    <!-- Header -->
    <header class="sticky top-4 z-50 flex justify-between items-center px-8 py-4 max-w-container-max-width mx-auto bg-surface/80 dark:bg-surface-container/80 backdrop-blur-md rounded-md mt-4 mx-4 w-[calc(100%-2rem)] shadow-[0_20px_40px_rgba(107,56,212,0.06)]">
        <div class="flex items-center gap-4">
            <span class="font-headline-lg text-headline-lg text-primary tracking-tight">{{ $headerTitle }}</span>
        </div>
        <div class="flex items-center gap-6">
            <div class="relative group">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant" data-icon="search">search</span>
                <input class="pl-12 pr-6 h-12 bg-surface-container-low border-none rounded-md w-64 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md" placeholder="Cari" type="text">
            </div>
            <button class="bg-primary/10 text-primary w-12 h-12 flex items-center justify-center rounded-md hover:bg-primary hover:text-white transition-all active:scale-95"><span class="material-symbols-outlined">notifications</span></button>
        </div>
    </header>
    <!-- Akhir Header -->
    <div class="p-gutter max-w-container-max-width mx-auto">
        {{ $slot }}
    </div>
</main>
    <!-- Bottom nav mobile -->
</body>
</html>