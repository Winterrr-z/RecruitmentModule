<div class="w-full px-gutter py-4">
    <!-- Hero Section -->
    <div class="text-center max-w-3xl mx-auto mb-8 mt-4">
        <h1 class="text-display-lg font-extrabold text-on-surface leading-tight mb-4 tracking-tight">
            Mulai Karir Hebat Anda di <span class="text-primary">{{ config('company.name') }}</span>
        </h1>
        <p class="text-body-lg text-on-surface-variant/80">
            Temukan posisi yang sesuai dengan bakat dan passion Anda. Mari bersama-sama membangun masa depan yang bermakna dan berdampak positif.
        </p>
    </div>

    <!-- Search & Filter Controls -->
    <x-advanced-filter searchPlaceholder="Cari jabatan atau departemen..." searchModel="search">
        <x-slot:filters>
            <!-- Tipe Kerja Filter -->
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Tipe Kerja</label>
                <select wire:model.live="selectedTipeKerja" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Tipe Kerja</option>
                    <option value="full-time">Full-time</option>
                    <option value="contract">Contract</option>
                </select>
            </div>

            <!-- Lokasi Filter -->
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Lokasi</label>
                <select wire:model.live="selectedLokasi" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Lokasi</option>
                    <option value="remote">Remote</option>
                    <option value="on-site">On-site</option>
                </select>
            </div>
        </x-slot:filters>
    </x-advanced-filter>

    <div class="mb-6">

        <!-- Active Filter States & Reset Button -->
        @if(!empty($search) || !empty($selectedTipeKerja) || !empty($selectedLokasi))
            <div class="flex justify-between items-center mt-4 pt-4 border-t border-surface-container-high">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-xs text-on-surface-variant/70 font-semibold uppercase tracking-wider">Filter Aktif:</span>
                    @if(!empty($search))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-primary/10 text-primary text-xs font-semibold">
                            Kata Kunci: "{{ $search }}"
                        </span>
                    @endif
                    @if(!empty($selectedTipeKerja))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-secondary-fixed text-on-secondary-fixed-variant text-xs font-semibold capitalize">
                            Tipe: {{ $selectedTipeKerja }}
                        </span>
                    @endif
                    @if(!empty($selectedLokasi))
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded bg-surface-container-highest text-on-surface text-xs font-semibold capitalize">
                            Lokasi: {{ $selectedLokasi }}
                        </span>
                    @endif
                </div>
                <button wire:click="resetFilters" class="inline-flex items-center gap-1.5 text-sm font-bold text-primary hover:text-primary-container transition-colors cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    <span>Bersihkan Filter</span>
                </button>
            </div>
        @endif
    </div>

    <!-- Career Cards List -->
    @if($vacancies->isEmpty())
        <div class="flex flex-col items-center justify-center p-16 text-center bg-white rounded-lg border border-dashed border-outline-variant/60 shadow-[0_20px_40px_rgba(107,56,212,0.02)]">
            <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center mb-4">
                <span class="material-symbols-outlined text-[36px] text-on-surface-variant/40">work_off</span>
            </div>
            <h3 class="text-title-md font-bold text-on-surface mb-2">
                @if(!empty($search) || !empty($selectedTipeKerja) || !empty($selectedLokasi))
                    Tidak menemukan lowongan yang sesuai dengan pencarian Anda.
                @else
                    Belum ada Lowongan saat ini.
                @endif
            </h3>
            <p class="text-body-md text-on-surface-variant/70 max-w-md mb-6">
                Silakan ubah kriteria pencarian Anda atau kembali lagi nanti untuk melihat posisi baru.
            </p>
            @if(!empty($search) || !empty($selectedTipeKerja) || !empty($selectedLokasi))
                <button wire:click="resetFilters" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-sm hover:bg-primary-container transition-all active:scale-95">
                    <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                    <span>Reset Semua Filter</span>
                </button>
            @endif
        </div>
    @else
        <!-- Jobs Count Grid Header -->
        <div class="flex justify-between items-center mb-6">
            <p class="text-sm text-on-surface-variant">
                Menampilkan <strong class="text-on-surface font-bold">{{ $vacancies->count() }}</strong> Lowongan pekerjaan
            </p>
        </div>

        <!-- Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($vacancies as $vacancy)
                <div class="bg-white rounded-lg border border-surface-container-high p-6 shadow-sm hover:shadow-md hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between">
                    <div>
                        <!-- Header Card -->
                        <div class="flex justify-between items-start gap-4 mb-4">
                            <span class="inline-flex px-2.5 py-0.5 rounded bg-primary/5 text-primary text-xs font-bold uppercase tracking-wide">
                                {{ $vacancy->department }}
                            </span>
                            <span class="text-xs text-on-surface-variant/50">
                                {{ $vacancy->created_at->diffForHumans() }}
                            </span>
                        </div>

                        <!-- Job Title -->
                        <h3 class="text-title-md font-bold text-on-surface mb-3 line-clamp-2 hover:text-primary transition-colors">
                            {{ $vacancy->title ?: $vacancy->job_title }}
                        </h3>

                        <!-- Badges -->
                        <div class="flex flex-wrap gap-2 mb-6">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded bg-primary-fixed text-on-primary-fixed-variant text-xs font-semibold capitalize">
                                <span class="material-symbols-outlined text-[14px]">work</span>
                                {{ $vacancy->employment_type }}
                            </span>
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded bg-secondary-fixed text-on-secondary-fixed-variant text-xs font-semibold capitalize">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>
                                {{ $vacancy->location }}
                            </span>
                        </div>

                        <!-- Details Section -->
                        <div class="space-y-3 pt-4 border-t border-surface-container-low text-sm text-on-surface-variant/80">
                            <!-- Salary Range -->
                            @if($vacancy->show_salary)
                                <div class="flex items-center gap-2.5 text-on-surface font-semibold">
                                    <span class="material-symbols-outlined text-primary text-[18px]">payments</span>
                                    <span>Rp {{ number_format($vacancy->estimated_salary_min, 0, ',', '.') }} - Rp {{ number_format($vacancy->estimated_salary_max, 0, ',', '.') }}</span>
                                </div>
                            @endif

                            <!-- Application Deadline -->
                            <div class="flex items-center gap-2.5">
                                <span class="material-symbols-outlined text-primary text-[18px]">calendar_month</span>
                                <span>Batas akhir: <strong class="text-on-surface font-semibold">{{ $vacancy->application_deadline ? $vacancy->application_deadline->translatedFormat('d F Y') : '-' }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- CTA -->
                    <div class="mt-6 pt-4 border-t border-surface-container-low">
                        <a href="{{ route('candidate.jobs.show', $vacancy->id) }}" 
                           class="w-full inline-flex items-center justify-center gap-2 px-5 py-3 bg-primary text-white font-bold rounded-sm hover:bg-primary-container shadow-[0_4px_12px_rgba(107,56,212,0.15)] hover:shadow-[0_4px_20px_rgba(107,56,212,0.25)] transition-all duration-200 active:scale-[0.98] no-underline text-sm">
                            <span>Lihat Detail</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
