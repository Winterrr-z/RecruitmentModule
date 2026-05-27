<div class="flex flex-col lg:flex-row gap-12">

    {{-- ======================= SIDEBAR FILTERS ======================= --}}
    <aside class="w-full lg:w-1/4 shrink-0 space-y-8">
        <div class="bg-surface-container-lowest rounded-lg p-6 soft-shadow sticky top-[120px]">
            <h2 class="font-title-md text-title-md mb-6 text-on-surface">Filters</h2>

            {{-- Search --}}
            <div class="mb-8 relative">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-outline-variant text-[22px]">search</span>
                <input wire:model.live.debounce.300ms="search"
                       class="w-full h-[56px] pl-12 pr-4 bg-surface-container-low border-none rounded-[24px] focus:bg-surface-container-lowest focus:ring-2 focus:ring-primary/20 transition-all font-body-md placeholder:text-outline text-on-surface"
                       placeholder="Cari posisi..."
                       type="text"/>
            </div>

            {{-- Department Filter --}}
            @if(!empty($departments))
                <div class="mb-8">
                    <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-4 tracking-widest">Departemen</h3>
                    <div class="space-y-3">
                        @foreach($departments as $dept => $count)
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input wire:model.live="selectedDepartments"
                                       value="{{ $dept }}"
                                       class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low"
                                       type="checkbox"/>
                                <span class="font-body-md text-on-surface group-hover:text-primary transition-colors">
                                    {{ $dept }} ({{ $count }})
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Job Type Filter --}}
            <div class="mb-8">
                <h3 class="font-label-sm text-label-sm text-on-surface-variant uppercase mb-4 tracking-widest">Tipe Kerja</h3>
                <div class="space-y-3">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input wire:model.live="selectedTypes"
                               value="full-time"
                               class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low"
                               type="checkbox"/>
                        <span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Full-time</span>
                    </label>
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <input wire:model.live="selectedTypes"
                               value="contract"
                               class="w-5 h-5 rounded-[6px] border-outline text-primary focus:ring-primary/20 bg-surface-container-low"
                               type="checkbox"/>
                        <span class="font-body-md text-on-surface group-hover:text-primary transition-colors">Contract</span>
                    </label>
                </div>
            </div>

            {{-- Reset Filters --}}
            @if(!empty($search) || !empty($selectedDepartments) || !empty($selectedTypes))
                <button wire:click="resetFilters"
                        class="w-full flex items-center justify-center gap-2 py-2.5 rounded-lg border border-primary/30 text-primary text-sm font-bold hover:bg-primary/5 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">restart_alt</span>
                    Reset Semua Filter
                </button>
            @endif
        </div>
    </aside>

    {{-- ======================= JOB LIST ======================= --}}
    <div class="w-full lg:w-3/4 space-y-6">

        {{-- List Header --}}
        <div class="flex justify-between items-center mb-8">
            <p class="font-body-lg text-body-lg text-on-surface-variant">
                Menampilkan
                <span class="font-semibold text-on-surface">{{ $lowongans->count() }}</span>
                posisi terbuka
            </p>
            <select wire:model.live="sortBy"
                    class="h-[48px] px-4 pr-10 bg-surface-container-lowest border-none rounded-full soft-shadow text-on-surface focus:ring-2 focus:ring-primary/20 font-body-md cursor-pointer">
                <option value="newest">Terbaru</option>
                <option value="oldest">Terlama</option>
            </select>
        </div>

        {{-- Empty State --}}
        @if($lowongans->isEmpty())
            <div class="flex flex-col items-center justify-center p-16 text-center bg-surface-container-lowest rounded-[32px] soft-shadow">
                <div class="w-16 h-16 rounded-full bg-surface-container flex items-center justify-center mb-4">
                    <span class="material-symbols-outlined text-[36px] text-on-surface-variant/40">work_off</span>
                </div>
                <h3 class="text-title-md font-bold text-on-surface mb-2">
                    @if(!empty($search) || !empty($selectedDepartments) || !empty($selectedTypes))
                        Tidak ada posisi yang cocok dengan filter Anda.
                    @else
                        Belum ada lowongan saat ini.
                    @endif
                </h3>
                <p class="text-body-md text-on-surface-variant/70 max-w-md mb-6">
                    Silakan ubah filter pencarian Anda atau kembali lagi nanti.
                </p>
                @if(!empty($search) || !empty($selectedDepartments) || !empty($selectedTypes))
                    <button wire:click="resetFilters"
                            class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-full hover:bg-primary/90 transition-all active:scale-95">
                        <span class="material-symbols-outlined text-[20px]">restart_alt</span>
                        Reset Filter
                    </button>
                @endif
            </div>

        {{-- Job Cards --}}
        @else
            @foreach($lowongans as $lowongan)
                <article class="bg-surface-container-lowest rounded-[32px] p-8 md:p-10 soft-shadow hover-lift flex flex-col md:flex-row md:items-center justify-between gap-6 group cursor-pointer relative overflow-hidden">

                    {{-- Hover left accent bar --}}
                    <div class="absolute left-0 top-0 bottom-0 w-2 bg-primary transform -translate-x-full group-hover:translate-x-0 transition-transform duration-300 rounded-l-[32px]"></div>

                    {{-- Job Info --}}
                    <div class="flex-grow space-y-4">
                        {{-- Badges Row --}}
                        <div class="flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full">
                                {{ $lowongan->departemen }}
                            </span>
                            @if($lowongan->tipe_kerja === 'full-time')
                                <span class="px-3 py-1 bg-primary/10 text-primary font-label-sm text-label-sm rounded-full">
                                    Full-time
                                </span>
                            @else
                                <span class="px-3 py-1 bg-tertiary-container/10 text-tertiary font-label-sm text-label-sm rounded-full border border-tertiary/20">
                                    Contract
                                </span>
                            @endif
                            <span class="px-3 py-1 bg-surface-container text-on-surface-variant font-label-sm text-label-sm rounded-full capitalize">
                                {{ $lowongan->lokasi }}
                            </span>
                        </div>

                        {{-- Job Title --}}
                        <h3 class="font-headline-lg text-headline-lg text-on-surface group-hover:text-primary transition-colors">
                            {{ $lowongan->jabatan }}
                        </h3>

                        {{-- Meta Row --}}
                        <div class="flex flex-col sm:flex-row sm:items-center gap-4 text-on-surface-variant font-body-md">
                            {{-- Deadline --}}
                            <span class="flex items-center gap-1">
                                <span class="material-symbols-outlined text-[20px]">calendar_month</span>
                                Batas: <strong class="text-on-surface ml-1">{{ $lowongan->application_deadline->translatedFormat('d F Y') }}</strong>
                            </span>

                            @if($lowongan->tampilkan_gaji && $lowongan->estimasi_gaji_min)
                                <span class="hidden sm:inline text-outline-variant">•</span>
                                <span class="flex items-center gap-1 text-primary">
                                    <span class="material-symbols-outlined text-[20px]">payments</span>
                                    Rp {{ number_format($lowongan->estimasi_gaji_min, 0, ',', '.') }}
                                    – Rp {{ number_format($lowongan->estimasi_gaji_max, 0, ',', '.') }}
                                </span>
                            @else
                                <span class="hidden sm:inline text-outline-variant">•</span>
                                <span class="flex items-center gap-1 text-on-surface-variant/70">
                                    <span class="material-symbols-outlined text-[20px]">payments</span>
                                    Gaji: Konfidensial
                                </span>
                            @endif
                        </div>

                        {{-- Contract note --}}
                        @if($lowongan->tipe_kerja === 'contract')
                            <p class="font-body-md text-label-sm text-tertiary bg-tertiary-container/5 inline-block px-3 py-1.5 rounded-lg border border-tertiary/10">
                                ● Posisi Berbasis Proyek · Kuota: {{ $lowongan->kuota }} orang
                            </p>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="flex md:flex-col gap-3 shrink-0">
                        <a href="{{ route('candidate.jobs.show', $lowongan->id) }}"
                           class="w-full md:w-auto text-center font-label-sm text-label-sm text-on-primary bg-primary px-8 py-4 rounded-full hover:bg-primary/90 transition-colors no-underline">
                            Lihat Detail
                        </a>
                        <button class="w-full md:w-auto font-label-sm text-label-sm text-primary bg-primary/10 px-8 py-4 rounded-full hover:bg-primary/20 transition-colors">
                            Simpan
                        </button>
                    </div>
                </article>
            @endforeach
        @endif
    </div>

</div>
