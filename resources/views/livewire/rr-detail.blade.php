<div>
    <!-- Main Detail Content -->
    <div class="px-gutter py-8 max-w-screen-xl mx-auto space-y-8">
        
        <!-- Flash Message Notification -->
        @if (session()->has('message'))
            <div class="p-4 rounded-lg bg-green-500/10 text-green-700 border border-green-500/20 flex items-center justify-between transition-all duration-300">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-green-600">check_circle</span>
                    <span class="font-body-md">{{ session('message') }}</span>
                </div>
                <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            </div>
        @endif

        <!-- Hero Title Section -->
        <section class="space-y-4">
            <nav class="flex items-center gap-2 mb-2" aria-label="Breadcrumb">
                <a href="{{ route('rr.index') }}" class="text-label-sm font-label-sm text-on-surface-variant hover:text-primary transition-colors">Recruitment Request</a>
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant/50">chevron_right</span>
                <span class="text-label-sm font-label-sm text-primary font-bold uppercase tracking-wider">RR Detail</span>
            </nav>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">{{ $lowongan->jabatan }}</h1>
                    
                    @if($lowongan->status === 'Ready to Publish')
                        <span class="px-4 py-1 bg-secondary-fixed text-on-secondary-fixed-variant text-label-sm font-label-sm rounded-md font-bold uppercase tracking-wider">Ready to Publish</span>
                    @elseif($lowongan->status === 'Published')
                        <span class="inline-flex items-center gap-1.5 px-4 py-1 bg-primary-fixed text-on-primary-fixed-variant text-label-sm font-label-sm rounded-md font-bold uppercase tracking-wider">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-primary"></span>
                            </span>
                            Published
                        </span>
                    @else
                        <span class="px-4 py-1 bg-surface-container-high text-on-surface-variant text-label-sm font-label-sm rounded-md font-bold uppercase tracking-wider border border-outline-variant">Completed</span>
                    @endif
                </div>
                
                @if($lowongan->mpp)
                    <span class="font-body-md text-sm text-on-surface-variant/70 font-semibold bg-surface-container-low border border-surface-container-high px-3 py-1.5 rounded-md self-start sm:self-auto">
                        Terhubung ke: <a href="{{ route('mpp.show', $lowongan->mpp_id) }}" class="text-primary hover:underline font-bold">MPP-{{ str_pad($lowongan->mpp_id, 3, '0', STR_PAD_LEFT) }} ({{ $lowongan->mpp->nama_plan }})</a>
                    </span>
                @endif
            </div>
        </section>

        <!-- Stats Grid Overview -->
        <section class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <!-- Total Pelamar -->
            <div class="bg-surface-container-lowest p-6 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.02)] flex flex-col justify-between">
                <p class="text-label-sm font-label-sm text-on-surface-variant/80 uppercase tracking-wider">Total Pelamar</p>
                <div class="text-headline-lg font-headline-lg text-on-surface mt-2">
                    {{ $totalCandidates }} <span class="text-xs font-semibold text-on-surface-variant/60">Orang</span>
                </div>
            </div>

            <!-- Pelamar Aktif -->
            <div class="bg-surface-container-lowest p-6 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.02)] flex flex-col justify-between">
                <p class="text-label-sm font-label-sm text-on-surface-variant/80 uppercase tracking-wider">Proses Aktif</p>
                <div class="text-headline-lg font-headline-lg text-primary mt-2">
                    {{ $activeCandidates }} <span class="text-xs font-semibold text-primary/70">Orang</span>
                </div>
            </div>

            <!-- Hired / Kuota -->
            <div class="bg-surface-container-lowest p-6 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.02)] flex flex-col justify-between">
                <p class="text-label-sm font-label-sm text-on-surface-variant/80 uppercase tracking-wider">Fulfillment Kuota</p>
                <div class="text-headline-lg font-headline-lg text-secondary mt-2">
                    {{ $hiredCandidates }} <span class="text-title-md font-semibold text-on-surface-variant/50">/ {{ $lowongan->kuota }} Orang</span>
                </div>
            </div>
        </section>

        <!-- Detailed Info Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
            
            <!-- Left 2/3 Content: Description & Specifications -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Deskripsi Pekerjaan -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.01)]">
                    <h4 class="font-title-md text-title-md mb-6 flex items-center gap-2 text-on-surface">
                        <span class="material-symbols-outlined text-primary">description</span>
                        Deskripsi Pekerjaan
                    </h4>
                    <div class="text-body-md text-on-surface whitespace-pre-line bg-surface-container-low/50 p-6 rounded-md border border-surface-container leading-relaxed">
                        {{ $lowongan->deskripsi_pekerjaan }}
                    </div>
                </div>

                <!-- Spesifikasi Kebutuhan -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.01)]">
                    <h4 class="font-title-md text-title-md mb-6 flex items-center gap-2 text-on-surface">
                        <span class="material-symbols-outlined text-primary">demography</span>
                        Spesifikasi Kebutuhan
                    </h4>
                    <div class="text-body-md text-on-surface whitespace-pre-line bg-surface-container-low/50 p-6 rounded-md border border-surface-container leading-relaxed">
                        {{ $lowongan->spesifikasi_kebutuhan ?: 'Tidak ada kualifikasi khusus yang dilampirkan.' }}
                    </div>
                </div>

                <!-- ATS Pipeline Visualizer -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.01)]">
                    <h4 class="font-title-md text-title-md mb-6 flex items-center gap-2 text-on-surface">
                        <span class="material-symbols-outlined text-primary">group</span>
                        Persebaran Kandidat per Tahapan ATS
                    </h4>
                    
                    <div class="space-y-4 mt-6">
                        @foreach($stages as $stageInfo)
                            <div>
                                <div class="flex justify-between items-center text-sm font-semibold mb-1">
                                    <span class="text-on-surface-variant">{{ $stageInfo['nama'] }}</span>
                                    <span class="text-primary">{{ $stageInfo['count'] }} Kandidat</span>
                                </div>
                                <div class="w-full bg-surface-container-low h-3 rounded-full overflow-hidden">
                                    @php
                                        $stagePercent = $totalCandidates > 0 ? round(($stageInfo['count'] / $totalCandidates) * 100) : 0;
                                    @endphp
                                    <div class="bg-primary h-full rounded-full transition-all duration-500" style="width: {{ $stagePercent }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-2 gap-4 mt-6 pt-6 border-t border-surface-container-low text-xs text-on-surface-variant/80">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-green-500 rounded-sm"></span>
                            <span>Hired: <strong>{{ $hiredCandidates }} Orang</strong></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 bg-red-500 rounded-sm"></span>
                            <span>Ditolak / CV Bank: <strong>{{ $rejectedCandidates }} Orang</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right 1/3 Content: Publication Info & Metadata -->
            <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-28">
                <!-- Detail Publikasi Card -->
                <div class="bg-surface-container-lowest p-8 rounded-md border border-surface-container-high shadow-[0px_20px_40px_-15px_rgba(107,56,212,0.01)] space-y-6">
                    <h4 class="font-title-md text-title-md pb-2 border-b border-surface-container-low flex items-center gap-2 text-on-surface">
                        <span class="material-symbols-outlined text-primary">info</span>
                        Informasi Publikasi
                    </h4>

                    <div class="space-y-4">
                        <!-- Tipe Kerja -->
                        <div class="flex items-start gap-3">
                            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">work</span>
                            <div>
                                <span class="text-xs text-on-surface-variant/60 block">Tipe Kerja</span>
                                <span class="font-body-md text-sm font-bold text-on-surface capitalize block mt-0.5">{{ $lowongan->tipe_kerja }}</span>
                            </div>
                        </div>

                        <!-- Lokasi Kerja -->
                        <div class="flex items-start gap-3 pt-3 border-t border-surface-container-low">
                            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">location_on</span>
                            <div>
                                <span class="text-xs text-on-surface-variant/60 block">Lokasi</span>
                                <span class="font-body-md text-sm font-bold text-on-surface capitalize block mt-0.5">{{ $lowongan->lokasi }}</span>
                            </div>
                        </div>

                        <!-- Application Deadline -->
                        <div class="flex items-start gap-3 pt-3 border-t border-surface-container-low">
                            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">calendar_month</span>
                            <div>
                                <span class="text-xs text-on-surface-variant/60 block">Batas Pendaftaran (Deadline)</span>
                                <span class="font-body-md text-sm font-bold text-on-surface block mt-0.5">
                                    {{ $lowongan->application_deadline->translatedFormat('d F Y') }}
                                </span>
                            </div>
                        </div>

                        <!-- Estimasi Gaji Internal -->
                        <div class="flex items-start gap-3 pt-3 border-t border-surface-container-low">
                            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">payments</span>
                            <div>
                                <span class="text-xs text-on-surface-variant/60 block">Estimasi Rentang Gaji</span>
                                <span class="font-body-md text-sm font-bold text-primary block mt-0.5">
                                    @if($lowongan->estimasi_gaji_min && $lowongan->estimasi_gaji_max)
                                        Rp {{ number_format($lowongan->estimasi_gaji_min, 0, ',', '.') }} - Rp {{ number_format($lowongan->estimasi_gaji_max, 0, ',', '.') }}
                                    @elseif($lowongan->estimasi_gaji_min)
                                        Rp {{ number_format($lowongan->estimasi_gaji_min, 0, ',', '.') }}
                                    @elseif($lowongan->estimasi_gaji_max)
                                        Rp {{ number_format($lowongan->estimasi_gaji_max, 0, ',', '.') }}
                                    @else
                                        Negosiasi
                                    @endif
                                </span>
                                <div class="mt-1">
                                    @if($lowongan->tampilkan_gaji)
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-green-700 bg-green-500/10 px-2 py-0.5 rounded">
                                            <span class="material-symbols-outlined text-[12px]">visibility</span>
                                            Tampil Publik
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-on-surface-variant/60 bg-surface-container-low px-2 py-0.5 rounded border border-surface-container">
                                            <span class="material-symbols-outlined text-[12px]">visibility_off</span>
                                            Disembunyikan
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Expected Join Date -->
                        <div class="flex items-start gap-3 pt-3 border-t border-surface-container-low">
                            <span class="material-symbols-outlined text-primary text-[20px] mt-0.5">date_range</span>
                            <div>
                                <span class="text-xs text-on-surface-variant/60 block">Expected Join Date</span>
                                <span class="font-body-md text-sm font-bold text-on-surface block mt-0.5">
                                    {{ $lowongan->expected_join_date ? $lowongan->expected_join_date->translatedFormat('d F Y') : '-' }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Bottom Reactive Action Area -->
        <section class="sticky bottom-8 left-0 right-0 z-40">
            <div class="bg-surface-container-lowest/80 backdrop-blur-xl border border-surface-container/50 p-6 rounded-md shadow-[0px_32px_64px_-16px_rgba(0,0,0,0.12)] flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <a class="flex items-center gap-2 text-primary font-bold hover:underline transition-all" href="{{ route('rr.index') }}">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span class="font-label-sm text-label-sm">Kembali ke Recruitment Request</span>
                    </a>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <!-- Action button berdasarkan status -->
                    @if($lowongan->status === 'Ready to Publish' && $totalCandidates === 0)
                        <!-- Tombol Edit Draft -->
                        <a href="{{ route('rr.edit', $lowongan->id) }}" class="px-6 h-14 bg-surface-container-low text-on-surface-variant hover:bg-surface-container border border-surface-container font-bold rounded-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                            <span>Edit Draft</span>
                        </a>

                        <!-- Tombol Hapus Draft -->
                        <button wire:click="delete" wire:confirm="Apakah Anda yakin ingin menghapus Recruitment Request ini?" class="px-6 h-14 bg-error/10 text-error hover:bg-error/20 border border-error/20 font-bold rounded-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                            <span>Hapus Draft</span>
                        </button>
                    @endif

                    @if($lowongan->status === 'Ready to Publish')
                        <button wire:click="publish" class="px-8 h-14 bg-primary text-white font-bold rounded-md shadow-[0px_8px_16px_-4px_rgba(107,56,212,0.3)] hover:bg-primary-container transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">rocket_launch</span>
                            <span>Aktifkan Lowongan</span>
                        </button>
                    @endif

                    @if($lowongan->status === 'Published')
                        <!-- Tombol Nonaktifkan Lowongan -->
                        <button wire:click="unpublish" wire:confirm="Nonaktifkan lowongan pekerjaan ini?" class="px-6 h-14 bg-surface-container-low text-on-surface-variant hover:bg-surface-container border border-surface-container font-bold rounded-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">visibility_off</span>
                            <span>Nonaktifkan Lowongan</span>
                        </button>

                        <!-- Tombol Tutup Lowongan -->
                        <button wire:click="close" wire:confirm="Tutup lowongan ini?" class="px-8 h-14 bg-[#ef4444] text-white font-bold rounded-md shadow-[0px_8px_16px_-4px_rgba(239,68,68,0.3)] hover:brightness-110 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">cancel</span>
                            <span>Tutup Lowongan</span>
                        </button>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
