<div>
    <!-- Flash Message Notification -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-green-500/10 text-green-700 border border-green-500/20 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="font-body-md">{{ session('message') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

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
            <div class="text-on-surface-variant font-label-sm text-label-sm uppercase mb-2">Completed/Closed</div>
            <div class="font-display-lg text-display-lg text-secondary">{{ $stats['completed'] }}</div>
        </div>
    </div>

    <!-- Table & Grid Controls -->
    <div class="mb-4 p-6 bg-surface-container-lowest rounded-md border border-surface-container-high shadow-[0_30px_40px_rgba(107,56,212,0.02)] flex flex-col md:flex-row justify-between items-center gap-6">
        <!-- Search Input -->
        <div class="relative w-full md:w-96">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[22px]">search</span>
            <input wire:model.live.debounce.300ms="search" class="w-full h-12 pl-12 pr-4 bg-surface-container-low border-none rounded-md font-body-md text-body-md focus:ring-2 focus:ring-primary-container focus:bg-surface-container-lowest transition-all" placeholder="Cari jabatan atau departemen..." type="text">
        </div>
        <!-- Status Filter Dropdown -->
        <div class="relative w-full md:w-64">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[22px]">filter_list</span>
            <select wire:model.live="status" class="w-full h-12 pl-12 pr-10 bg-surface-container-low border-none rounded-md font-body-md text-body-md focus:ring-2 focus:ring-primary-container focus:bg-surface-container-lowest transition-all appearance-none cursor-pointer text-on-surface-variant">
                <option value="">Semua Status</option>
                <option value="Ready to Publish">Ready to Publish</option>
                <option value="Published">Published</option>
                <option value="Completed/Closed">Completed</option>
            </select>
            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-on-surface-variant text-[20px]">keyboard_arrow_down</span>
        </div>
    </div>

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

    @if($lowongans->isEmpty())
        <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-md border border-dashed border-outline-variant/50 shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.04)]">
            <span class="material-symbols-outlined text-[64px] text-on-surface-variant/30 mb-4">description</span>
            <h3 class="text-title-md font-title-md text-on-surface mb-2">Belum Ada Recruitment Request</h3>
            <p class="text-label-sm font-label-sm text-on-surface-variant max-w-md mb-6">
                Tidak ada data lowongan pekerjaan yang ditemukan dengan kriteria saat ini.
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($lowongans as $lowongan)
                <div onclick="window.location='{{ route('rr.show', $lowongan->id) }}'" class="cursor-pointer block group bg-surface-container-lowest p-6 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.04)] hover:shadow-[0px_35px_60px_-15px_rgba(107,56,212,0.08)] hover:-translate-y-1 transition-all duration-300 flex flex-col justify-between text-on-surface">
                    <div>
                        <!-- Badge status -->
                        <div class="flex justify-between items-start mb-4">
                            @if($lowongan->status === 'Ready to Publish')
                                <span class="inline-flex items-center px-3 py-1 rounded bg-secondary-fixed text-on-secondary-fixed-variant font-label-sm text-[10px] font-bold uppercase tracking-wider">
                                    Ready to Publish
                                </span>
                            @elseif($lowongan->status === 'Published')
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-primary-fixed text-on-primary-fixed-variant font-label-sm text-[10px] font-bold uppercase tracking-wider">
                                    <span class="relative flex h-2 w-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                                    </span>
                                    Published
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded bg-surface-container-high text-on-surface-variant font-label-sm text-[10px] font-bold uppercase tracking-wider border border-outline-variant">
                                    Completed
                                </span>
                            @endif

                            @if($lowongan->mpp)
                                <span class="font-body-md text-xs text-on-surface-variant/60 font-semibold bg-surface-container-low px-2 py-0.5 rounded-[8px]">
                                    MPP-{{ str_pad($lowongan->mpp_id, 3, '0', STR_PAD_LEFT) }}
                                </span>
                            @endif
                        </div>

                        <!-- Judul dan Departemen -->
                        <div class="mb-4">
                            <h4 class="text-title-md font-title-md font-bold text-on-surface group-hover:text-primary transition-colors line-clamp-2">
                                {{ $lowongan->jabatan }}
                            </h4>
                            <p class="text-label-sm font-label-sm text-on-surface-variant/80 mt-1">
                                {{ $lowongan->departemen }}
                            </p>
                        </div>

                        <!-- Detail lowongan -->
                        <div class="grid grid-cols-2 gap-y-3 gap-x-2 py-4 my-2 border-t border-b border-surface-container-low font-body-md text-sm text-on-surface-variant">
                            <!-- Tipe Kerja -->
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">work</span>
                                <span class="capitalize">{{ $lowongan->tipe_kerja }}</span>
                            </div>
                            <!-- Lokasi -->
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">location_on</span>
                                <span class="capitalize">{{ $lowongan->lokasi }}</span>
                            </div>
                            <!-- Application Deadline -->
                            <div class="flex items-center gap-2 col-span-2">
                                <span class="material-symbols-outlined text-primary text-[18px]">calendar_month</span>
                                <span>Deadline: <strong class="text-on-surface font-semibold">{{ $lowongan->application_deadline->translatedFormat('d F Y') }}</strong></span>
                            </div>
                        </div>
                    </div>

                    <!-- Kuota dan Aksi Footer -->
                    <div class="mt-4 flex flex-col gap-4">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-on-surface-variant">Kuota Terbuka:</span>
                            <span class="font-bold @if($lowongan->kuota > 0) text-on-surface @else text-error @endif bg-surface-container-low px-3 py-1 rounded">
                                {{ $lowongan->kuota }} Orang
                            </span>
                        </div>

                        @if($lowongan->status === 'Ready to Publish' || $lowongan->status === 'Published')
                            <div class="flex items-center justify-end gap-2 pt-3 border-t border-surface-container-low">
                                <!-- Tombol Edit (Draft & Tanpa Pelamar) -->
                                @if($lowongan->status === 'Ready to Publish' && $lowongan->candidates_count === 0)
                                    <a href="{{ route('rr.edit', $lowongan->id) }}" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-surface-container-high border border-outline-variant text-on-surface-variant font-label-sm text-xs font-semibold hover:bg-surface-container-highest shadow-sm transition-all active:scale-95 no-underline">
                                        <span class="material-symbols-outlined text-[16px]">edit</span>
                                        <span>Edit</span>
                                    </a>
                                @endif

                                <!-- Tombol Hapus (Draft & Tanpa Pelamar) -->
                                @if($lowongan->status === 'Ready to Publish' && $lowongan->candidates_count === 0)
                                    <button wire:click="delete({{ $lowongan->id }})" wire:confirm="Apakah Anda yakin ingin menghapus Recruitment Request ini?" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-error/10 text-error font-label-sm text-xs font-semibold hover:bg-error/20 transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-[16px]">delete</span>
                                        <span>Hapus</span>
                                    </button>
                                @endif

                                <!-- Tombol Aktifkan -->
                                @if($lowongan->status === 'Ready to Publish')
                                    <button wire:click="publish({{ $lowongan->id }})" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-primary text-on-primary font-label-sm text-xs font-semibold hover:bg-primary-container shadow-sm transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-[16px]">rocket_launch</span>
                                        <span>Aktifkan</span>
                                    </button>
                                @endif

                                <!-- Tombol Nonaktifkan -->
                                @if($lowongan->status === 'Published')
                                    <button wire:click="unpublish({{ $lowongan->id }})" wire:confirm="Apakah Anda yakin ingin menonaktifkan lowongan pekerjaan ini?" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-surface-container-high border border-outline-variant text-on-surface-variant font-label-sm text-xs font-semibold hover:bg-surface-container-highest shadow-sm transition-all active:scale-95">
                                        <span class="material-symbols-outlined text-[16px]">visibility_off</span>
                                        <span>Nonaktifkan</span>
                                    </button>
                                @endif

                                <!-- Tombol Tutup -->
                                @if($lowongan->status === 'Published')
                                    <button wire:click="close({{ $lowongan->id }})" wire:confirm="Apakah Anda yakin ingin menutup lowongan pekerjaan ini?" onclick="event.stopPropagation()" class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2 rounded-md bg-error/15 text-error font-label-sm text-xs font-semibold hover:bg-error/25 transition-all active:scale-95">
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
            {{ $lowongans->links() }}
        </div>
    @endif
    </div>
</div>
