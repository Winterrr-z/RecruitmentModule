<div>
    <x-breadcrumb :items="[['label' => 'Settings', 'url' => null]]" />
    <div class="max-w-3xl mx-auto w-full">
        <!-- Settings Header -->
        <div class="mb-8">
            <div class="flex items-center gap-3 mb-2">
                <span class="material-symbols-outlined text-primary text-[32px]">settings</span>
                <h1 class="text-2xl font-bold text-on-surface">Pengaturan Akun</h1>
            </div>
            <p class="text-on-surface-variant text-body-md">Kelola pengaturan akun dan preferensi Anda</p>
        </div>

        <!-- Logout Section -->
        <div class="bg-surface-container-lowest rounded-md border border-surface-container-high p-6 shadow-sm">
            <div class="mb-4">
                <h2 class="text-title-md font-title-md text-on-surface mb-1">Keluar dari Akun</h2>
                <p class="text-label-sm font-label-sm text-on-surface-variant">
                    Anda akan keluar dari semua perangkat dan sesi Anda akan berakhir.
                </p>
            </div>

            <button
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-error text-on-error font-bold rounded-md hover:bg-error/90 transition-all active:scale-95 shadow-sm">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                <span>Keluar</span>
            </button>
        </div>
    </div>

    <!-- Hidden Logout Form -->
    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>
</div>