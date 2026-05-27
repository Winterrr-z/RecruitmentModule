<!-- Left Sidebar -->
<aside class="w-64 min-h-screen bg-surface border-r border-outline-variant flex flex-col fixed left-0 top-0 z-50">
<div class="p-gutter mb-8">
<a class="flex items-center gap-3 active:scale-95 transition-transform group" href="#">
<img alt="Human First Logo" class="h-8 w-auto" src="https://lh3.googleusercontent.com/aida/ADBb0ugrpLiJy26Io_mLDAQUQEnf730xr_rABFyyY9ICKsaSPA5_GH1W8-QK1fD0RYxjtYsrzgxiLqamtB5Cf7PHSU2VVk-26EclV5EbaORiivGaTvJaDE89sPUodINDL5bX3qnXtwACKPwFJXWkJbiN7pI5K01QjstbO5c1JVQz0Jm3F0f4WyfPpHri7TSjpW7g0ybWdcDeq5FdFZNOE0vVonSrcR27cT44HXIFCkDQuHadqH2jtq-eCa8vl_BA"/>
<span class="font-display text-lg font-bold text-primary tracking-tight">Human First</span>
</a>
</div>
<nav class="flex flex-col gap-2 px-4 flex-grow">
<a class="flex items-center gap-3 px-4 py-3 text-on-surface-variant hover:bg-surface-container hover:text-primary transition-colors rounded-lg font-medium" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span class="">Dashboard</span>
</a>
<a class="flex items-center gap-3 px-4 py-3 sidebar-active rounded-lg font-semibold" href="#">
<span class="material-symbols-outlined">work</span>
<span class="">Jobs</span>
</a>
</nav>
<div class="p-4 border-t border-outline-variant">
<div class="flex items-center gap-3 px-2 py-2">
<div class="w-8 h-8 rounded-full overflow-hidden">
<img alt="User profile avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB96TNKBo_GteAubzLOXfZg6fOrFoQ6tfMNhFyN0QHQ5X4JXs9YOMa8MJFVgmpk7n5VMXVq3X0wo6mEREq3XakuAHxnb9Hk3URb5cUNgQkmy5eVMUEbipCz3MumSnAHGnWZHWMzozA7tovejh56j2a5wkIv5ClyP-t5ffMO0TYvLlsxSUHo1ApTJ8WWIBW9Lsxm1DBo00BlRLQ6-l7iF85B5ncMRXguHMi-QYeA13U-rWDN60wJ4i1BCAFXlIOqMHb3KfAUO6JeMOef"/>
</div>
<div class="flex flex-col overflow-hidden">
<span class="text-sm font-bold truncate text-on-surface">Admin Utama</span>
<span class="text-xs text-on-surface-variant truncate">admin@humanfirst.com</span>
</div>
</div>
</div>
</aside>
<!-- Main Content -->
<main class="flex-grow ml-64 min-h-screen flex flex-col">
<!-- Header with User Avatar -->
<header class="h-[88px] w-full flex items-center justify-end px-gutter bg-surface/80 backdrop-blur-md sticky top-0 z-40">
<div class="flex items-center gap-4">
<button class="w-10 h-10 rounded-full overflow-hidden border-2 border-primary/20 hover:border-primary transition-all">
<img alt="User avatar" class="w-full h-full object-cover" src="https://lh3.googleusercontent.com/aida-public/AB6AXuB96TNKBo_GteAubzLOXfZg6fOrFoQ6tfMNhFyN0QHQ5X4JXs9YOMa8MJFVgmpk7n5VMXVq3X0wo6mEREq3XakuAHxnb9Hk3URb5cUNgQkmy5eVMUEbipCz3MumSnAHGnWZHWMzozA7tovejh56j2a5wkIv5ClyP-t5ffMO0TYvLlsxSUHo1ApTJ8WWIBW9Lsxm1DBo00BlRLQ6-l7iF85B5ncMRXguHMi-QYeA13U-rWDN60wJ4i1BCAFXlIOqMHb3KfAUO6JeMOef"/>
</button>
</div>
</header>
<!-- Main Content Grid -->
<section class="px-gutter py-section-padding-desktop max-w-7xl mx-auto w-full">
<div class="flex flex-col lg:flex-row gap-12">
<!-- Sidebar Filters -->
<aside class="w-full lg:w-1/4 shrink-0 space-y-8">
<div class="bg-surface-container-lowest rounded-lg p-6 soft-shadow sticky top-[120px]">
<h2 class="font-title-md text-title-md mb-6">Filters</h2>
<!-- Search -->
<div class="mb-8 relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant">search</span>
<input class="w-full h-[56px] pl-12 pr-4 bg-surface-container-low border-none rounded-[24px] focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all font-body-md placeholder:text-outline" placeholder="Search roles..." type="text"/>
</div>
<!-- Department -->
<div class="mb-8">
<h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-4 tracking-widest">Department</h3>
<div class="space-y-3">
<label class="flex items-center gap-3 cursor-pointer group">
<input checked="" class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low" type="checkbox"/>
<span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Engineering (12)</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low" type="checkbox"/>
<span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Design (4)</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low" type="checkbox"/>
<span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Product (6)</span>
</label>
</div>
</div>
<!-- Job Type -->
<div class="mb-8">
<h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-4 tracking-widest">Job Type</h3>
<div class="space-y-3">
<label class="flex items-center gap-3 cursor-pointer group">
<input class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low" type="checkbox"/>
<span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Full-time</span>
</label>
<label class="flex items-center gap-3 cursor-pointer group">
<input checked="" class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low" type="checkbox"/>
<span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Contract</span>
</label>
</div>
</div>
</div>
</aside>
<!-- Job List -->
<div class="w-full lg:w-3/4 space-y-6">
<!-- Header -->
<div class="flex justify-between items-center mb-8">
<p class="font-body-lg text-body-lg text-on-surface-variant">Showing <span class="font-semibold text-on-surface">22</span> open roles</p>
<select class="h-[48px] px-4 pr-10 bg-surface-container-lowest border-none rounded-full soft-shadow text-on-surface focus:ring-2 focus:ring-primary/20">
<option>Most Relevant</option>
<option>Newest</option>
</select>
</div>
<!-- Job Card 1 -->
<article class="bg-surface-container-lowest rounded-[32px] p-8 md:p-10 soft-shadow hover-lift flex flex-col md:flex-row md:items-center justify-between gap-6 group cursor-pointer relative overflow-hidden">
<div class="absolute left-0 top-0 bottom-0 w-2 bg-primary transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300 rounded-l-[32px]"></div>
<div class="flex-grow space-y-4">
<div class="flex flex-wrap gap-2 mb-2">
<span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full">Engineering</span>
<span class="px-3 py-1 bg-primary/10 text-primary font-label-sm text-label-sm rounded-full">Full-time</span>
<span class="px-3 py-1 bg-secondary-container/20 text-secondary font-label-sm text-label-sm rounded-full">Remote</span>
</div>
<h3 class="font-headline-lg text-headline-lg text-on-surface">Senior Frontend Engineer</h3>
<div class="flex flex-col sm:flex-row sm:items-center gap-4 text-on-surface-variant font-body-md">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[20px]">location_on</span> Global (Remote)</span>
<span class="hidden sm:inline text-outline-variant">•</span>
<span class="flex items-center gap-1 text-primary"><span class="material-symbols-outlined text-[20px]">payments</span> $120k - $145k</span>
</div>
</div>
<div class="flex md:flex-col gap-3 shrink-0">
<button class="w-full md:w-auto font-label-sm text-label-sm text-on-primary bg-primary px-8 py-4 rounded-full hover:bg-primary/90 transition-colors">Apply Now</button>
<button class="w-full md:w-auto font-label-sm text-label-sm text-primary bg-primary/10 px-8 py-4 rounded-full hover:bg-primary/20 transition-colors">View Detail</button>
</div>
</article>
<!-- Job Card 2 -->
<article class="bg-surface-container-lowest rounded-[32px] p-8 md:p-10 soft-shadow hover-lift flex flex-col md:flex-row md:items-center justify-between gap-6 group cursor-pointer relative overflow-hidden">
<div class="absolute left-0 top-0 bottom-0 w-2 bg-primary transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300 rounded-l-[32px]"></div>
<div class="flex-grow space-y-4">
<div class="flex flex-wrap gap-2 mb-2">
<span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full">Design</span>
<span class="px-3 py-1 bg-tertiary-container/10 text-tertiary font-label-sm text-label-sm rounded-full border border-tertiary/20">Contract</span>
<span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full">Hybrid</span>
</div>
<h3 class="font-headline-lg text-headline-lg text-on-surface">Senior Product Designer</h3>
<div class="flex flex-col sm:flex-row sm:items-center gap-4 text-on-surface-variant font-body-md">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[20px]">location_on</span> Jakarta, Indonesia</span>
<span class="hidden sm:inline text-outline-variant">•</span>
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[20px]">payments</span> Salary: Confidential</span>
</div>
<p class="font-body-md text-label-sm text-tertiary bg-tertiary-container/5 inline-block px-3 py-1.5 rounded-lg border border-tertiary/10">
                            ● Posisi Berbasis Proyek (Masa Kerja: 6 Bulan)
                        </p>
</div>
<div class="flex md:flex-col gap-3 shrink-0">
<button class="w-full md:w-auto font-label-sm text-label-sm text-on-primary bg-primary px-8 py-4 rounded-full hover:bg-primary/90 transition-colors">Apply Now</button>
<button class="w-full md:w-auto font-label-sm text-label-sm text-primary bg-primary/10 px-8 py-4 rounded-full hover:bg-primary/20 transition-colors">View Detail</button>
</div>
</article>
<!-- Job Card 3 -->
<article class="bg-surface-container-lowest rounded-[32px] p-8 md:p-10 soft-shadow hover-lift flex flex-col md:flex-row md:items-center justify-between gap-6 group cursor-pointer relative overflow-hidden">
<div class="absolute left-0 top-0 bottom-0 w-2 bg-primary transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300 rounded-l-[32px]"></div>
<div class="flex-grow space-y-4">
<div class="flex flex-wrap gap-2 mb-2">
<span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full">Product</span>
<span class="px-3 py-1 bg-primary/10 text-primary font-label-sm text-label-sm rounded-full">Full-time</span>
<span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full">On-site</span>
</div>
<h3 class="font-headline-lg text-headline-lg text-on-surface">HR Systems Analyst</h3>
<div class="flex flex-col sm:flex-row sm:items-center gap-4 text-on-surface-variant font-body-md">
<span class="flex items-center gap-1"><span class="material-symbols-outlined text-[20px]">location_on</span> Singapore</span>
<span class="hidden sm:inline text-outline-variant">•</span>
<span class="flex items-center gap-1 text-primary"><span class="material-symbols-outlined text-[20px]">payments</span> $90k - $110k</span>
</div>
</div>
<div class="flex md:flex-col gap-3 shrink-0">
<button class="w-full md:w-auto font-label-sm text-label-sm text-on-primary bg-primary px-8 py-4 rounded-full hover:bg-primary/90 transition-colors">Apply Now</button>
<button class="w-full md:w-auto font-label-sm text-label-sm text-primary bg-primary/10 px-8 py-4 rounded-full hover:bg-primary/20 transition-colors">View Detail</button>
</div>
</article>
</div>
</div>
</section>
<!-- Footer -->
<footer class="mt-auto bg-surface-container-low dark:bg-inverse-surface w-full rounded-t-lg">
<div class="flex flex-col md:flex-row justify-between items-center px-gutter py-section-padding-desktop max-w-7xl mx-auto gap-8">
<div class="font-display-lg text-title-md font-bold text-primary opacity-80 hover:opacity-100">
                Human First
            </div>
<div class="flex flex-wrap justify-center gap-6">
<a class="font-body-md text-body-md text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim underline transition-all" href="#">Privacy Policy</a>
<a class="font-body-md text-body-md text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim underline transition-all" href="#">Terms of Service</a>
<a class="font-body-md text-body-md text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim underline transition-all" href="#">Cookie Settings</a>
<a class="font-body-md text-body-md text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim underline transition-all" href="#">LinkedIn</a>
<a class="font-body-md text-body-md text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim underline transition-all" href="#">Twitter</a>
<a class="font-body-md text-body-md text-on-surface-variant dark:text-outline-variant hover:text-primary dark:hover:text-primary-fixed-dim underline transition-all" href="#">Instagram</a>
</div>
<div class="font-body-md text-body-md text-on-surface dark:text-inverse-on-surface text-center md:text-right">
                © 2024 Human First. All rights reserved.
            </div>
</div>
</footer>
