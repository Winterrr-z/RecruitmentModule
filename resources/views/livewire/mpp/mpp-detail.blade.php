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
                <a href="{{ route('mpp.index') }}" class="text-label-sm font-label-sm text-on-surface-variant hover:text-primary transition-colors">Manpower Planning</a>
                <span class="material-symbols-outlined text-[16px] text-on-surface-variant/50">chevron_right</span>
                <span class="text-label-sm font-label-sm text-primary font-bold uppercase tracking-wider">MPP Detail</span>
            </nav>
            <div class="flex items-center gap-4">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">{{ $mpp->nama_plan }}</h1>
                
                @if(strtolower($mpp->status) === 'approved')
                    <span class="px-4 py-1 bg-green-100 text-green-800 text-label-sm font-label-sm rounded-md font-bold uppercase tracking-wider">Approved</span>
                @elseif(strtolower($mpp->status) === 'completed' || strtolower($mpp->status) === 'closed')
                    <span class="px-4 py-1 bg-blue-100 text-blue-800 text-label-sm font-label-sm rounded-md font-bold uppercase tracking-wider">Completed</span>
                @else
                    <span class="px-4 py-1 bg-surface-container-highest text-on-surface-variant text-label-sm font-label-sm rounded-md font-bold uppercase tracking-wider">Draft</span>
                @endif
            </div>
        </section>

        <!-- Step Indicator -->
        <section class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.06)] border border-surface-container/30">
            @php
                $isCompleted = in_array(strtolower($mpp->status), ['completed', 'closed']);
                
                $isDraftActive = !$isCompleted;
                $isApprovedActive = (strtolower($mpp->status) === 'approved' || $hasLowongan) && !$isCompleted;
                $isLowonganActive = $hasLowongan && !$isCompleted;
            @endphp
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6 md:gap-8">
                <!-- Step 1: Draft -->
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $isDraftActive ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-surface-container-high text-on-surface-variant' }}">
                        @if($isApprovedActive)
                            <span class="material-symbols-outlined text-[18px]">check</span>
                        @else
                            1
                        @endif
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm font-bold {{ $isDraftActive ? 'text-primary' : 'text-on-surface-variant/70' }}">Draft</p>
                        <p class="text-xs text-on-surface-variant/70">Planning dibuat</p>
                    </div>
                </div>

                <!-- Line 1 -->
                <div class="hidden md:block flex-1 h-0.5 transition-all duration-300 {{ $isApprovedActive ? 'bg-primary' : 'bg-surface-container-high' }}"></div>

                <!-- Step 2: Approved -->
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $isApprovedActive ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-surface-container-high text-on-surface-variant' }}">
                        @if($isLowonganActive)
                            <span class="material-symbols-outlined text-[18px]">check</span>
                        @else
                            2
                        @endif
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm font-bold {{ $isApprovedActive ? 'text-primary' : 'text-on-surface-variant/70' }}">Approved</p>
                        <p class="text-xs text-on-surface-variant/70">Disetujui oleh HR</p>
                    </div>
                </div>

                <!-- Line 2 -->
                <div class="hidden md:block flex-1 h-0.5 transition-all duration-300 {{ $isLowonganActive ? 'bg-primary' : 'bg-surface-container-high' }}"></div>

                <!-- Step 3: Lowongan Dibuat -->
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $isLowonganActive ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-surface-container-high text-on-surface-variant' }}">
                        3
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm font-bold {{ $isLowonganActive ? 'text-primary' : 'text-on-surface-variant/70' }}">Lowongan Dibuat</p>
                        <p class="text-xs text-on-surface-variant/70">Proses recruitment aktif</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Urgency Progress Section -->
        <section class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.06)] border border-surface-container/30 space-y-6">
            @php
                $now = now();
                $target = \Carbon\Carbon::parse($mpp->target_waktu_absolut);
                $created = \Carbon\Carbon::parse($mpp->created_at);
                
                $totalDays = $created->diffInDays($target);
                $elapsedDays = $created->diffInDays($now);
                $daysRemaining = $now->diffInDays($target, false);
                
                if ($totalDays > 0) {
                    $percent = min(100, max(0, round(($elapsedDays / $totalDays) * 100)));
                } else {
                    $percent = 100;
                }
                
                $isUrgent = $daysRemaining <= 14 && $daysRemaining > 0;
                $isOverdue = $daysRemaining <= 0;

                if ($daysRemaining > 0) {
                    if ($daysRemaining >= 30) {
                        $sisaWaktuFormatted = round($daysRemaining / 30) . ' Bulan';
                    } elseif ($daysRemaining >= 7) {
                        $sisaWaktuFormatted = round($daysRemaining / 7) . ' Minggu';
                    } else {
                        $sisaWaktuFormatted = $daysRemaining . ' Hari';
                    }
                } else {
                    $sisaWaktuFormatted = 'Waktu Habis';
                }
            @endphp
            <div class="flex justify-between items-end">
                <div class="space-y-1">
                    <h3 class="font-title-md text-title-md text-on-surface">Urgency &amp; Fulfillment Progress</h3>
                    <p class="text-label-sm font-label-sm text-on-surface-variant">
                        Status: 
                        @if($isOverdue)
                            <span class="text-error font-bold">Waktu Habis</span>
                        @elseif($isUrgent)
                            <span class="text-error font-bold">High Urgency</span>
                        @else
                            <span class="text-primary font-bold">Normal Urgency</span>
                        @endif
                        (Batas Waktu: {{ $target->translatedFormat('d M Y') }})
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-display-lg font-display-lg {{ $isOverdue || $isUrgent ? 'text-error' : 'text-primary' }}">{{ $percent }}%</span>
                    <p class="text-label-sm font-label-sm {{ $isOverdue || $isUrgent ? 'text-error' : 'text-primary' }} uppercase tracking-widest">Waktu Berjalan</p>
                </div>
            </div>
            <div class="w-full bg-surface-container-high rounded-full h-4 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-1000 {{ $isOverdue || $isUrgent ? 'bg-error' : 'bg-primary' }}" style="width: {{ $percent }}%"></div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 pt-4 border-t border-surface-container/50">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full {{ $isOverdue || $isUrgent ? 'bg-error-container text-error' : 'bg-primary/10 text-primary' }} flex items-center justify-center">
                        <span class="material-symbols-outlined">timer</span>
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Sisa Waktu</p>
                        <p class="font-title-md text-title-md font-bold">
                            {{ $sisaWaktuFormatted }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-primary">
                        <span class="material-symbols-outlined">person_search</span>
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Kandidat Direkrut</p>
                        <p class="font-title-md text-title-md font-bold">0 / {{ $mpp->jumlah_kebutuhan }} Orang</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-surface-container flex items-center justify-center text-secondary">
                        <span class="material-symbols-outlined">pending_actions</span>
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Proses Seleksi</p>
                        <p class="font-title-md text-title-md font-bold">0 Aktif</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Detailed Info Grid -->
        <section class="grid grid-cols-1 gap-8">
            <div class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.04)] border border-surface-container/30">
                <h4 class="font-title-md text-title-md mb-8 flex items-center gap-2 text-on-surface">
                    <span class="material-symbols-outlined text-primary">info</span>
                    Informasi Detail Jabatan
                </h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-y-8 gap-x-12">
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Departemen</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->departemen }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Jabatan</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->jabatan }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Kuota Dibutuhkan</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->jumlah_kebutuhan }} Orang</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Estimasi Gaji</p>
                        <p class="font-body-lg text-body-lg font-semibold text-primary">
                            @if($mpp->estimasi_gaji_min && $mpp->estimasi_gaji_max)
                                Rp {{ number_format($mpp->estimasi_gaji_min, 0, ',', '.') }} - Rp {{ number_format($mpp->estimasi_gaji_max, 0, ',', '.') }}
                            @elseif($mpp->estimasi_gaji_min)
                                Rp {{ number_format($mpp->estimasi_gaji_min, 0, ',', '.') }}
                            @elseif($mpp->estimasi_gaji_max)
                                Rp {{ number_format($mpp->estimasi_gaji_max, 0, ',', '.') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">SLA Perencanaan</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->sla_bulan }} Bulan</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Target Selesai</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ \Carbon\Carbon::parse($mpp->target_waktu_absolut)->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Tanggal Dibuat</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                </div>
            </div>

            <!-- Deskripsi / Catatan Tambahan Card -->
            <div class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.04)] border border-surface-container/30">
                <h4 class="font-title-md text-title-md mb-6 flex items-center gap-2 text-on-surface">
                    <span class="material-symbols-outlined text-primary">description</span>
                    Deskripsi / Catatan Perencanaan
                </h4>
                <div class="text-body-md text-on-surface whitespace-pre-line bg-surface-container-low/50 p-6 rounded-md border border-surface-container">
                    {{ $mpp->note ?: 'Tidak ada deskripsi atau catatan tambahan.' }}
                </div>
            </div>
        </section>

        <!-- Bottom Reactive Action Area -->
        <section class="sticky bottom-8 left-0 right-0 z-40">
            <div class="bg-surface-container-lowest/80 backdrop-blur-xl border border-surface-container/50 p-6 rounded-md shadow-[0px_32px_64px_-16px_rgba(0,0,0,0.12)] flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <a class="flex items-center gap-2 text-primary font-bold hover:underline transition-all" href="{{ route('mpp.index') }}">
                        <span class="material-symbols-outlined">arrow_back</span>
                        <span class="font-label-sm text-label-sm">Kembali ke Manpower Planning</span>
                    </a>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <!-- Edit Button -->
                    <a href="{{ route('mpp.edit', $mpp->id) }}" class="px-6 h-14 bg-surface-container-low text-on-surface-variant hover:bg-surface-container border border-surface-container font-bold rounded-md transition-all active:scale-95 flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">edit</span>
                        <span>Edit Plan</span>
                    </a>

                    <!-- Approve Button -->
                    @if(strtolower($mpp->status) === 'draft')
                        <button wire:click="approve" wire:confirm="Approve MPP ini?" class="px-8 h-14 bg-[#10b981] text-white font-bold rounded-md shadow-[0px_8px_16px_-4px_rgba(16,185,129,0.3)] hover:brightness-110 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Approve Perencanaan</span>
                        </button>
                    @endif

                    <!-- Buat Lowongan Button -->
                    @if(strtolower($mpp->status) === 'approved' && !$hasLowongan)
                        <a href="{{ route('rr.create', ['mpp_id' => $mpp->id]) }}" class="px-8 h-14 bg-primary text-white font-bold rounded-md shadow-[0px_8px_16px_-4px_rgba(107,56,212,0.3)] hover:bg-primary-container transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">add_box</span>
                            <span>Buat Lowongan</span>
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>
