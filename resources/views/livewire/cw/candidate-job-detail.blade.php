<div class="py-8">
    {{-- Breadcrumb / Back --}}
    <div class="mb-6">
        <a href="{{ auth()->check() ? route('candidate.jobs') : route('careers') }}" 
           class="inline-flex items-center gap-1.5 text-sm font-semibold text-on-surface-variant/80 hover:text-primary transition-colors no-underline">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Kembali ke Daftar Lowongan
        </a>
    </div>

    {{-- Layout Split --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        
        {{-- Sisi Kiri: Detail Lowongan --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Header Detail --}}
            <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-[0_4px_20px_rgba(107,56,212,0.02)]">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold bg-primary/10 text-primary mb-4 capitalize">
                    <span class="material-symbols-outlined text-[14px]">work</span>
                    {{ $lowongan->tipe_kerja }}
                </span>
                
                <h1 class="text-3xl font-extrabold text-on-surface tracking-tight leading-tight mb-2">
                    {{ $lowongan->jabatan }}
                </h1>
                
                <div class="flex flex-wrap gap-x-6 gap-y-3 text-sm text-on-surface-variant font-medium mt-4">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[18px]">corporate_fare</span>
                        {{ $lowongan->departemen }}
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                        <span class="capitalize">{{ $lowongan->lokasi }}</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-primary text-[18px]">calendar_today</span>
                        Batas Akhir: {{ $lowongan->application_deadline->format('d M Y') }}
                    </span>
                </div>

                @if ($lowongan->tampilkan_gaji && ($lowongan->estimasi_gaji_min || $lowongan->estimasi_gaji_max))
                    <div class="mt-6 pt-6 border-t border-surface-container-high flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-emerald-500/10 text-emerald-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">payments</span>
                        </div>
                        <div>
                            <p class="text-xs text-on-surface-variant/70 font-semibold uppercase tracking-wider">Estimasi Gaji</p>
                            <p class="text-lg font-bold text-on-surface">
                                @if ($lowongan->estimasi_gaji_min && $lowongan->estimasi_gaji_max)
                                    Rp {{ number_format($lowongan->estimasi_gaji_min, 0, ',', '.') }} - Rp {{ number_format($lowongan->estimasi_gaji_max, 0, ',', '.') }}
                                @elseif ($lowongan->estimasi_gaji_min)
                                    Mulai dari Rp {{ number_format($lowongan->estimasi_gaji_min, 0, ',', '.') }}
                                @else
                                    Hingga Rp {{ number_format($lowongan->estimasi_gaji_max, 0, ',', '.') }}
                                @endif
                            </p>
                        </div>
                    </div>
                @endif
            </div>

            {{-- Deskripsi Pekerjaan --}}
            <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-[0_4px_20px_rgba(107,56,212,0.02)]">
                <h2 class="text-lg font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">description</span>
                    Deskripsi Pekerjaan
                </h2>
                <div class="prose prose-sm max-w-none text-on-surface-variant leading-relaxed whitespace-pre-line">
                    {{ $lowongan->deskripsi_pekerjaan }}
                </div>
            </div>

            {{-- Kualifikasi / Spesifikasi --}}
            <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-[0_4px_20px_rgba(107,56,212,0.02)]">
                <h2 class="text-lg font-bold text-on-surface mb-4 flex items-center gap-2">
                    <span class="material-symbols-outlined text-primary text-[20px]">star</span>
                    Kualifikasi & Persyaratan
                </h2>
                <div class="prose prose-sm max-w-none text-on-surface-variant leading-relaxed whitespace-pre-line">
                    {{ $lowongan->spesifikasi_kebutuhan }}
                </div>
            </div>
        </div>

        {{-- Sisi Kanan: Form Lamaran / Sticky Card --}}
        <div class="lg:sticky lg:top-24">
            
            @if (auth()->check())
                @if (auth()->user()->role === 'applicant')
                    @if ($hasActiveApplication)
                        <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center shadow-[0_4px_20px_rgba(239,68,68,0.03)]">
                            <span class="material-symbols-outlined text-red-600 text-[36px] mb-3">error</span>
                            <h3 class="text-md font-bold text-red-800 mb-1">Anda Memiliki Lamaran Aktif</h3>
                            <p class="text-sm text-red-700 leading-relaxed mb-4">
                                Anda hanya dapat melamar 1 posisi dalam satu waktu. Selesaikan proses seleksi pada lamaran Anda sebelumnya terlebih dahulu.
                            </p>
                            <a href="{{ route('candidate.dashboard') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-red-600 hover:bg-red-700 text-white font-bold rounded-lg text-sm transition-colors w-full no-underline">
                                <span class="material-symbols-outlined text-[18px]">person</span>
                                Lihat Lamaran Saya
                            </a>
                        </div>
                    @else
                        {{-- Form Lamaran --}}
                        <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-[0_20px_50px_rgba(107,56,212,0.05)]">
                            <h2 class="text-lg font-bold text-on-surface mb-4 flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[22px]">send</span>
                                Kirim Lamaran
                            </h2>

                        <form wire:submit="apply" class="space-y-4" novalidate>
                            {{-- Nama (Readonly) --}}
                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                                    Nama Lengkap
                                </label>
                                <input type="text" 
                                       value="{{ $nama }}" 
                                       readonly 
                                       class="w-full h-11 px-4 rounded-full bg-surface-container-lowest border border-surface-container-high text-on-surface-variant/80 font-medium cursor-not-allowed">
                            </div>

                            {{-- Email (Readonly) --}}
                            <div>
                                <label class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                                    Alamat Email
                                </label>
                                <input type="email" 
                                       value="{{ $email }}" 
                                       readonly 
                                       class="w-full h-11 px-4 rounded-full bg-surface-container-lowest border border-surface-container-high text-on-surface-variant/80 font-medium cursor-not-allowed">
                            </div>

                            {{-- Telepon --}}
                            <div>
                                <label for="form-phone" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                                    Nomor Telepon <span class="text-error">*</span>
                                </label>
                                <input type="text" 
                                       id="form-phone"
                                       wire:model="telepon"
                                       placeholder="Contoh: 08123456789" 
                                       class="w-full h-11 px-4 rounded-full bg-surface-container-low border @error('telepon') border-error bg-error/5 @else border-transparent @enderror focus:border-primary/40 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm font-medium placeholder:text-on-surface-variant/40 text-on-surface">
                                @error('telepon')
                                    <p class="mt-1 ml-4 text-xs text-error flex items-center gap-1 font-semibold">
                                        <span class="material-symbols-outlined text-[13px]">error</span>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- CV PDF --}}
                            <div>
                                <label for="form-cv" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                                    CV (Curriculum Vitae) <span class="text-error">*</span>
                                </label>
                                <div class="relative">
                                    <input type="file" 
                                           id="form-cv"
                                           wire:model="cv"
                                           accept=".pdf"
                                           class="hidden">
                                    <label for="form-cv" class="w-full h-11 px-4 rounded-full bg-surface-container-low border @error('cv') border-error bg-error/5 @else border-transparent @enderror hover:border-primary/30 cursor-pointer flex items-center gap-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-all">
                                        <span class="material-symbols-outlined text-[20px]">upload_file</span>
                                        <span class="truncate">
                                            @if ($cv)
                                                {{ $cv->getClientOriginalName() }}
                                            @else
                                                Pilih file PDF (Maks. 5MB)
                                            @endif
                                        </span>
                                    </label>
                                </div>
                                @error('cv')
                                    <p class="mt-1 ml-4 text-xs text-error flex items-center gap-1 font-semibold">
                                        <span class="material-symbols-outlined text-[13px]">error</span>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Portofolio PDF --}}
                            <div>
                                <label for="form-portfolio" class="block text-xs font-bold text-on-surface-variant uppercase tracking-wider mb-1">
                                    Portofolio <span class="text-on-surface-variant/40 font-normal">(Opsional)</span>
                                </label>
                                <div class="relative">
                                    <input type="file" 
                                           id="form-portfolio"
                                           wire:model="portofolio"
                                           accept=".pdf"
                                           class="hidden">
                                    <label for="form-portfolio" class="w-full h-11 px-4 rounded-full bg-surface-container-low border @error('portofolio') border-error bg-error/5 @else border-transparent @enderror hover:border-primary/30 cursor-pointer flex items-center gap-2 text-sm font-medium text-on-surface-variant hover:text-primary transition-all">
                                        <span class="material-symbols-outlined text-[20px]">upload_file</span>
                                        <span class="truncate">
                                            @if ($portofolio)
                                                {{ $portofolio->getClientOriginalName() }}
                                            @else
                                                Pilih file PDF (Maks. 5MB)
                                            @endif
                                        </span>
                                    </label>
                                </div>
                                @error('portofolio')
                                    <p class="mt-1 ml-4 text-xs text-error flex items-center gap-1 font-semibold">
                                        <span class="material-symbols-outlined text-[13px]">error</span>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <div class="pt-2">
                                <button type="submit"
                                        wire:loading.attr="disabled"
                                        class="w-full h-11 rounded-full bg-primary text-white font-bold text-sm tracking-wide hover:bg-primary-container shadow-[0_4px_16px_rgba(107,56,212,0.25)] hover:shadow-[0_4px_24px_rgba(107,56,212,0.35)] transition-all active:scale-[0.98] disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                                    <span wire:loading.remove wire:target="apply">
                                        Kirim Lamaran
                                    </span>
                                    <span wire:loading wire:target="apply" class="inline-flex items-center justify-center gap-2">
                                        <svg class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif
                @else
                    {{-- Logged in but not applicant (e.g. HR) --}}
                    <div class="bg-amber-500/10 border border-amber-500/20 rounded-2xl p-6 text-center shadow-[0_4px_20px_rgba(245,158,11,0.03)]">
                        <span class="material-symbols-outlined text-amber-600 text-[36px] mb-3">lock_open</span>
                        <h3 class="text-md font-bold text-amber-800 mb-1">Akses Dibatasi</h3>
                        <p class="text-sm text-amber-700 leading-relaxed">
                            Hanya akun Pelamar yang dapat melamar pekerjaan ini.
                        </p>
                    </div>
                @endif
            @else
                {{-- Guest: Action Call to Login/Register --}}
                <div class="bg-white rounded-2xl p-6 border border-surface-container-high shadow-[0_20px_50px_rgba(107,56,212,0.05)] text-center">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-primary/10 text-primary mb-3">
                        <span class="material-symbols-outlined text-[24px]">assignment_ind</span>
                    </div>
                    <h3 class="text-lg font-bold text-on-surface mb-2">Ingin Melamar Pekerjaan Ini?</h3>
                    <p class="text-sm text-on-surface-variant leading-relaxed mb-6">
                        Silakan masuk dengan akun Pelamar Anda untuk mengirimkan lamaran dan memantau status seleksi.
                    </p>

                    <div class="flex flex-col gap-3">
                        <a href="{{ route('candidate.login') }}" 
                           class="w-full h-11 rounded-full bg-primary text-white font-bold text-sm tracking-wide hover:bg-primary-container shadow-[0_4px_16px_rgba(107,56,212,0.25)] hover:shadow-[0_4px_24px_rgba(107,56,212,0.35)] transition-all flex items-center justify-center gap-2 no-underline">
                            <span class="material-symbols-outlined text-[18px]">login</span>
                            Masuk Akun
                        </a>
                        <a href="{{ route('candidate.register') }}" 
                           class="w-full h-11 rounded-full bg-surface-container-low hover:bg-surface-container-high border border-transparent font-bold text-sm text-primary hover:text-primary transition-all flex items-center justify-center gap-2 no-underline">
                            Daftar Akun Baru
                        </a>
                    </div>
                </div>
            @endif

        </div>

    </div>
</div>
