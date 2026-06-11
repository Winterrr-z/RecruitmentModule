<div>
    <x-breadcrumb :items="[['label' => 'Recruitment Request', 'url' => null]]" />
    <x-toast-alert />

    <!-- Content Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">List Recruitment Request</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Kelola dan publikasikan lowongan pekerjaan berdasarkan rencana tenaga kerja yang telah disetujui.</p>
        </div>
        <a href="{{ route('rr.create') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Tambah Request</span>
        </a>
    </div>

    <!-- Stats/Filter Overview -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-4">
        <div class="bg-surface-container-lowest rounded-md p-6 shadow-[0_30px_40px_rgba(107,56,212,0.03)] border border-surface-container-high flex flex-col justify-between">
            <div class="text-on-surface-variant font-label-sm text-label-sm uppercase mb-2">Total Active</div>
            <div class="font-display-lg text-display-lg text-primary">{{ $stats['total_active'] }}</div>
        </div>
        <div class="bg-surface-container-lowest rounded-md p-6 shadow-[0_30px_40px_rgba(107,56,212,0.03)] border border-surface-container-high flex flex-col justify-between">
            <div class="text-on-surface-variant font-label-sm text-label-sm uppercase mb-2">Ready to Publish</div>
            <div class="font-display-lg text-display-lg text-on-surface">{{ $stats['ready_to_publish'] }}</div>
        </div>
        <div class="bg-surface-container-lowest rounded-md p-6 shadow-[0_30px_40px_rgba(107,56,212,0.03)] border border-surface-container-high flex flex-col justify-between">
            <div class="text-on-surface-variant font-label-sm text-label-sm uppercase mb-2">Completed / Closed</div>
            <div class="font-display-lg text-display-lg text-secondary">{{ $stats['completed'] }}</div>
        </div>
    </div>

    <!-- Table & Grid Controls -->
    <x-advanced-filter searchPlaceholder="Cari jabatan atau departemen..." searchModel="search">
        <x-slot:filters>
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Status</label>
                <select wire:model.live="status" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Ready to Publish">Ready to Publish</option>
                    <option value="Published">Published</option>
                    <option value="Completed">Completed</option>
                    <option value="Closed">Closed</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Urutkan</label>
                <select wire:model.live="sortBy" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="newest">Terbaru (Latest)</option>
                    <option value="oldest">Terlama (Oldest)</option>
                </select>
            </div>
        </x-slot:filters>
    </x-advanced-filter>

    <!-- Cards Grid Container with Loading State -->
    <div class="relative min-h-[300px]">
        <!-- Loading overlay -->
        <div wire:loading.delay.longer class="absolute inset-0 bg-white/50 dark:bg-surface/50 backdrop-blur-xs flex items-center justify-center z-50 rounded-lg">
            <div class="flex items-center gap-3 px-5 py-3 bg-surface-container-lowest text-primary font-bold rounded-lg border border-surface-container-high shadow-lg">
                <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm font-semibold">Memuat data...</span>
            </div>
        </div>

    @if($rrs->isEmpty())
        <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-md border border-dashed border-outline-variant/50 shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.04)]">
            <span class="material-symbols-outlined text-[64px] text-on-surface-variant/30 mb-4">description</span>
            <h3 class="text-title-md font-title-md text-on-surface mb-2">Belum Ada Recruitment Request</h3>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($rrs as $rr)
                @php
                    $rrStatusVal = $rr->status instanceof \App\Enums\RrStatus ? $rr->status->value : $rr->status;
                    $normalizedStatus = strtolower(trim($rrStatusVal));
                    $isCompleted = in_array($normalizedStatus, ['completed/closed', 'completed', 'closed']);
                @endphp
                <div onclick="window.location='{{ route('rr.show', $rr->id) }}'" class="cursor-pointer block group p-6 rounded-md border transition-all duration-300 flex flex-col justify-between text-on-surface
                    {{ $isCompleted 
                        ? 'bg-surface-container-low border-surface-container/60 opacity-70 grayscale shadow-none' 
                        : 'bg-surface-container-lowest border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.04)] hover:shadow-[0px_35px_60px_-15px_rgba(107,56,212,0.08)] hover:-translate-y-1' }}">
                    <div>
                        <!-- Badge status -->
                        <div class="flex justify-between items-start mb-4">
                            @if($normalizedStatus === 'ready to publish')
                                <span class="inline-flex items-center px-3 py-1 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-[10px] font-bold uppercase tracking-wider">
                                    Ready to Publish
                                </span>
                            @elseif($normalizedStatus === 'published')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-[10px] font-bold uppercase tracking-wider">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                                    </span>
                                    Published
                                </span>
                            @elseif($normalizedStatus === 'closed')
                                <span class="inline-flex items-center px-3 py-1 rounded bg-error/10 text-error font-label-sm text-[10px] font-bold uppercase tracking-wider border border-error/20">
                                    Closed
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded bg-green-100 text-green-800 font-label-sm text-[10px] font-bold uppercase tracking-wider border border-green-200">
                                    Completed
                                </span>
                            @endif

                            @if($rr->mpp)
                                <span class="font-body-md text-xs text-on-surface-variant/60 font-semibold bg-surface-container-low px-2 py-0.5 rounded-[8px]">
                                    MPP-{{ str_pad($rr->mpp_id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            @endif
                        </div>

                        <!-- Judul dan Departemen -->
                        <div class="mb-4">
                            <h4 class="text-title-md font-title-md font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2">
                                {{ $rr->job_title }}
                            </h4>
                            <p class="text-label-sm font-label-sm text-on-surface-variant/80 mt-1">
                                {{ $rr->department }}
                            </p>
                        </div>

                        <!-- Detail vacancy -->
                        <div class="grid grid-cols-2 gap-y-3 gap-x-2 py-4 my-2 border-t border-b border-surface-container-low font-body-md text-sm text-on-surface-variant">
                            <!-- Tipe Kerja -->
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">work</span>
                                <span class="capitalize">{{ $rr->employment_type }}</span>
                            </div>
                            <!-- Lokasi -->
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                                <span class="capitalize">{{ $rr->location }}</span>
                            </div>
                            <!-- Application Deadline -->
                            <div class="flex items-center gap-2 col-span-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">calendar_month</span>
                                <span>Deadline: <strong class="text-on-surface font-semibold">{{ $rr->application_deadline->translatedFormat('d F Y') }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kuota dan Aksi Footer -->
                    <div class="mt-4 flex flex-col gap-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Kuota Terbuka:</span>
                            <span class="font-bold @if($rr->quota > 0) text-on-surface @else text-error @endif bg-surface-container-low px-3 py-1 rounded">
                                {{ $rr->quota }} Orang
                            </span>
                        </div>

                        @if($normalizedStatus === 'ready to publish' || $normalizedStatus === 'published')
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-surface-container-low">
                                <!-- Tombol Edit (Draft & Tanpa Pelamar) -->
                                @if($normalizedStatus === 'ready to publish' && $rr->candidates_count === 0)
                                    <a href="{{ route('rr.edit', $rr->id) }}" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-surface-container-high border border-outline-variant text-on-surface-variant font-label-sm text-xs font-semibold hover:bg-surface-container-highest shadow-sm transition-all active:scale-95 no-underline">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        <span>Edit</span>
                                    </a>
                                @endif

                                <!-- Tombol Aktifkan -->
                                @if($normalizedStatus === 'ready to publish')
                                    <button wire:click="publish({{ $rr->id }})" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-primary text-on-primary font-label-sm text-xs font-semibold hover:bg-primary-container shadow-sm transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-[16px]">rocket_launch</span>
                                        <span>Aktifkan</span>
                                    </button>
                                @endif

                                <!-- Tombol Nonaktifkan -->
                                @if($normalizedStatus === 'published')
                                    <button wire:click="unpublish({{ $rr->id }})" wire:confirm="Apakah Anda yakin ingin menonaktifkan lowongan pekerjaan ini?" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-surface-container-high border border-outline-variant text-on-surface-variant font-label-sm text-xs font-semibold hover:bg-surface-container-highest shadow-sm transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-[16px]">visibility_off</span>
                                        <span>Nonaktifkan</span>
                                    </button>
                                @endif

                                <!-- Tombol Tutup -->
                                @if($normalizedStatus === 'ready to publish')
                                    <button wire:click="close({{ $rr->id }})" wire:confirm="Apakah Anda yakin ingin menutup lowongan pekerjaan ini?" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-error/15 text-error font-label-sm text-xs font-semibold hover:bg-error/25 transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-[16px]">cancel</span>
                                        <span>Tutup</span>
                                    </button>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $rrs->links() }}
        </div>
    @endif
    </div>
</div>