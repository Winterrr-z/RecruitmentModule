<div>
    <!-- Content Header -->
    <div class="mb-8">
        <h2 class="font-headline-lg text-headline-lg text-on-surface flex items-center gap-3">
            <span class="material-symbols-outlined text-[32px] text-primary">account_circle</span>
            <span>Profil Saya</span>
        </h2>
        <p class="font-body-md text-body-md text-on-surface-variant/70 mt-1">Informasi detail profil akun HR Anda di sistem rekrutmen.</p>
    </div>

    <div class="max-w-3xl space-y-6">

        <!-- Profile Header Card -->
        <div class="bg-surface-container-lowest rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 overflow-hidden">
            <!-- Banner Gradient -->
            <div class="h-28 bg-gradient-to-r from-primary via-primary/80 to-primary-container relative">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_20%,rgba(255,255,255,0.15),transparent_60%)]"></div>
            </div>

            <div class="px-8 pb-8">
                <!-- Avatar & Name Section -->
                <div class="flex flex-col sm:flex-row items-start sm:items-end gap-5 -mt-12">
                    <!-- Avatar -->
                    <div class="relative">
                        <div class="w-24 h-24 rounded-full bg-primary-container ring-4 ring-surface-container-lowest flex items-center justify-center shadow-lg overflow-hidden">
                            @if($user->profile_photo_path)
                                <img src="{{ Storage::url($user->profile_photo_path) }}" class="w-full h-full object-cover" alt="Foto Profil">
                            @else
                                <span class="material-symbols-outlined text-primary text-[48px]">person</span>
                            @endif
                        </div>
                        <div class="absolute -bottom-1 -right-1 w-7 h-7 bg-green-500 rounded-full border-[3px] border-surface-container-lowest flex items-center justify-center">
                            <span class="material-symbols-outlined text-white text-[14px]">check</span>
                        </div>
                    </div>

                    <!-- Name & Role -->
                    <div class="flex-1 sm:pb-1">
                        <h3 class="text-xl font-extrabold text-on-surface tracking-tight">{{ $user->name }}</h3>
                        <p class="text-sm text-on-surface-variant mt-0.5 flex items-center gap-1.5">
                            <span class="material-symbols-outlined text-[16px] text-primary">verified</span>
                            <span class="font-medium uppercase tracking-wider text-xs text-primary">HR Staff — Terverifikasi</span>
                        </p>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center gap-3 mt-4 sm:mt-0">
                        <a href="{{ route('hr.profile.password') }}"
                           class="inline-flex items-center gap-2 px-5 h-11 border border-outline/35 text-on-surface-variant font-bold rounded-md hover:text-primary hover:border-primary/30 hover:bg-primary/5 transition-all active:scale-95 text-sm">
                            <span class="material-symbols-outlined text-[18px]">lock</span>
                            Ubah Password
                        </a>
                        <a href="{{ route('hr.profile.edit') }}"
                           class="inline-flex items-center gap-2 px-5 h-11 bg-primary text-white font-bold rounded-md hover:bg-primary-container hover:shadow-lg transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)] text-sm">
                            <span class="material-symbols-outlined text-[18px]">edit</span>
                            Edit Profil
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Profile Detail Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <!-- Informasi Pribadi Card -->
            <div class="bg-surface-container-lowest rounded-md shadow-[0px_20px_40px_-10px_rgba(107,56,212,0.04)] border border-surface-container/30 p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[20px]">person</span>
                    </div>
                    <h4 class="text-title-md font-bold text-on-surface">Informasi Pribadi</h4>
                </div>

                <div class="space-y-4">
                    <!-- Nama -->
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">badge</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Nama Lengkap</p>
                            <p class="text-sm font-semibold text-on-surface truncate">{{ $user->name }}</p>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">mail</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Alamat Email</p>
                            <p class="text-sm font-semibold text-on-surface truncate">{{ $user->email }}</p>
                        </div>
                    </div>

                    <!-- Telepon -->
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">call</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Nomor Telepon</p>
                            @if($user->phone_number)
                                <p class="text-sm font-semibold text-on-surface">{{ $user->phone_number }}</p>
                            @else
                                <p class="text-sm text-on-surface-variant/40 italic">Belum diisi</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Jabatan Card -->
            <div class="bg-surface-container-lowest rounded-md shadow-[0px_20px_40px_-10px_rgba(107,56,212,0.04)] border border-surface-container/30 p-6">
                <div class="flex items-center gap-2 mb-5">
                    <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                        <span class="material-symbols-outlined text-primary text-[20px]">work</span>
                    </div>
                    <h4 class="text-title-md font-bold text-on-surface">Informasi Jabatan</h4>
                </div>

                <div class="space-y-4">
                    <!-- Departemen -->
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">corporate_fare</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Departemen</p>
                            @if($user->departemen)
                                <p class="text-sm font-semibold text-on-surface">{{ $user->departemen }}</p>
                            @else
                                <p class="text-sm text-on-surface-variant/40 italic">Belum diisi</p>
                            @endif
                        </div>
                    </div>

                    <!-- Job Title -->
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">work</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Nama Jabatan</p>
                            @if($user->job_title)
                                <p class="text-sm font-semibold text-on-surface">{{ $user->job_title }}</p>
                            @else
                                <p class="text-sm text-on-surface-variant/40 italic">Belum diisi</p>
                            @endif
                        </div>
                    </div>

                    <!-- Role -->
                    <div class="flex items-start gap-3">
                        <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">shield_person</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Peran Sistem</p>
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary uppercase tracking-wider">
                                <span class="material-symbols-outlined text-[14px]">admin_panel_settings</span>
                                {{ $user->role ?? 'HR' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Account Info Footer Card -->
        <div class="bg-surface-container-lowest rounded-md shadow-[0px_20px_40px_-10px_rgba(107,56,212,0.04)] border border-surface-container/30 p-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-[20px]">security</span>
                </div>
                <h4 class="text-title-md font-bold text-on-surface">Informasi Akun</h4>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <!-- Bergabung Sejak -->
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">calendar_today</span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Bergabung Sejak</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $user->created_at?->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                </div>

                <!-- Terakhir Diperbarui -->
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">update</span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Terakhir Diperbarui</p>
                        <p class="text-sm font-semibold text-on-surface">{{ $user->updated_at?->translatedFormat('d F Y') ?? '-' }}</p>
                    </div>
                </div>

                <!-- Status -->
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-on-surface-variant/50 text-[20px] mt-0.5">check_circle</span>
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/60 mb-0.5">Status Akun</p>
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                            Aktif
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
