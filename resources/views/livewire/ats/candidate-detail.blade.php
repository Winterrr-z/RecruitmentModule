<div>
    <!-- Flash Notifications -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-green-500/10 text-green-700 border border-green-500/20 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="font-body-md text-sm font-semibold">{{ session('message') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 rounded-lg bg-error/10 text-error border border-error/20 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-error">warning</span>
                <span class="font-body-md text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-error hover:text-error/80 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- Content Header & Back button -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ route('ats.dashboard') }}" class="p-2 hover:bg-surface-container rounded-md transition-colors text-on-surface-variant flex items-center" title="Kembali ke Dashboard">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Detail Profil Pelamar</h2>
                <p class="font-body-md text-body-md text-on-surface-variant/70">Manajemen profil, jadwal interview, dan scorecard evaluasi pelamar</p>
            </div>
        </div>

        @if (($candidate->currentStage->id === 2 || strtolower($candidate->currentStage->nama) === 'final') && in_array($candidate->status, ['Applied', 'In Progress', 'Offered']) && $candidate->lowongan && $candidate->lowongan->kuota > 0)
            <a href="{{ route('ats.offering.send', ['candidateId' => $candidate->id]) }}" 
               class="inline-flex items-center justify-center gap-2 px-5 h-11 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)] text-sm">
                <span class="material-symbols-outlined text-[20px]">mail</span>
                <span>Kirim Offering Letter</span>
            </a>
        @endif
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start mb-8">
        
        <!-- Left Column: Profile Card -->
        <div class="bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
            <div class="flex flex-col items-center text-center pb-6 border-b border-surface-container-high/40">
                <div class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-2xl mb-4">
                    {{ strtoupper(substr($candidate->nama, 0, 2)) }}
                </div>
                <h3 class="text-title-md font-headline-lg text-on-surface mb-1">{{ $candidate->nama }}</h3>
                <p class="text-label-sm font-label-sm text-on-surface-variant/70 mb-3">ID: #{{ $candidate->id }}</p>
                
                <!-- Status & Stage badge -->
                <div class="flex flex-wrap gap-2 justify-center">
                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-xs font-bold">
                        {{ $candidate->currentStage->nama }}
                    </span>
                    @switch($candidate->status)
                        @case('Rejected')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-950/20 text-red-950 border border-red-950/30 rounded-full text-xs font-bold">
                                Rejected
                            </span>
                            @break
                        @case('Applied')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-blue-500/10 text-blue-700 border border-blue-500/20 rounded-full text-xs font-bold">
                                Applied
                            </span>
                            @break
                        @case('In Progress')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-surface-container-high text-on-surface-variant border border-surface-container rounded-full text-xs font-bold">
                                In Progress
                            </span>
                            @break
                        @case('Offered')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-amber-500/10 text-amber-700 border border-amber-500/20 rounded-full text-xs font-bold">
                                Offered
                            </span>
                            @break
                        @case('Hired')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-500/10 text-green-700 border border-green-500/20 rounded-full text-xs font-bold">
                                Hired
                            </span>
                            @break
                        @case('Declined')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-500/10 text-red-700 border border-red-500/20 rounded-full text-xs font-bold">
                                Declined
                            </span>
                            @break
                        @case('Expired')
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-500/10 text-red-700 border border-red-500/20 rounded-full text-xs font-bold">
                                Expired
                            </span>
                            @break
                        @default
                            <span class="inline-flex items-center gap-1 px-3 py-1 bg-surface-container-high text-on-surface-variant border border-surface-container rounded-full text-xs font-bold">
                                {{ $candidate->status }}
                            </span>
                    @endswitch
                </div>
            </div>

            <!-- Profile Data Fields -->
            <div class="space-y-4">
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Email</span>
                    <span class="text-body-md text-on-surface font-semibold">{{ $candidate->email }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Telepon</span>
                    <span class="text-body-md text-on-surface font-semibold">{{ $candidate->telepon }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Lowongan Pekerjaan</span>
                    <span class="text-body-md text-primary font-bold">{{ $candidate->lowongan?->jabatan ?: 'Kandidat Mandiri' }}</span>
                    <div class="text-[11px] text-on-surface-variant/60">{{ $candidate->lowongan?->departemen ?: 'Tanpa Lowongan' }}</div>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Sumber Pendaftaran</span>
                    <span class="text-body-md text-on-surface font-semibold capitalize">{{ $candidate->source }}</span>
                </div>
            </div>

            <!-- Profile Documents Download Buttons -->
            <div class="pt-4 border-t border-surface-container-high/40 flex flex-col gap-3">
                <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Lampiran Dokumen</span>
                
                <!-- CV Link -->
                @if($candidate->cv_path)
                    <button wire:click="downloadCv" class="w-full flex items-center justify-between px-4 py-3 bg-surface-container hover:bg-surface-container-high/80 rounded-md text-on-surface font-semibold text-sm transition-all">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-primary">description</span>
                            <span>Download CV</span>
                        </span>
                        <span class="material-symbols-outlined text-[18px]">download</span>
                    </button>
                @else
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant/50 px-4 py-3 bg-surface-container/30 border border-dashed rounded-md">
                        <span class="material-symbols-outlined text-[20px]">description</span>
                        <span>CV belum diunggah</span>
                    </div>
                @endif

                <!-- Portofolio Link -->
                @if($candidate->portofolio_path)
                    <button wire:click="downloadPortofolio" class="w-full flex items-center justify-between px-4 py-3 bg-surface-container hover:bg-surface-container-high/80 rounded-md text-on-surface font-semibold text-sm transition-all">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px] text-primary">work</span>
                            <span>Download Portofolio</span>
                        </span>
                        <span class="material-symbols-outlined text-[18px]">download</span>
                    </button>
                @else
                    <div class="flex items-center gap-2 text-xs text-on-surface-variant/50 px-4 py-3 bg-surface-container/30 border border-dashed rounded-md">
                        <span class="material-symbols-outlined text-[20px]">work</span>
                        <span>Portofolio belum diunggah</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Column: Movements History List -->
        <div class="lg:col-span-2 bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
            <div>
                <h3 class="text-title-md font-headline-lg text-on-surface mb-1">Riwayat Pergerakan Tahap</h3>
                <p class="text-body-md text-xs text-on-surface-variant/70">Catatan pergerakan tahap seleksi pelamar ini dari waktu ke waktu</p>
            </div>

            <div class="overflow-hidden border border-surface-container rounded-md">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-surface-container-high bg-surface-container-low/40">
                                <th class="px-4 py-3.5 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant">Dari Stage</th>
                                <th class="px-4 py-3.5 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant">Ke Stage</th>
                                <th class="px-4 py-3.5 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant">Tanggal</th>
                                <th class="px-4 py-3.5 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-surface-container/30">
                            @forelse($movements as $m)
                                <tr>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-on-surface font-semibold">
                                        {{ $m->fromStage->nama }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-primary font-bold">
                                        {{ $m->toStage->nama }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap text-sm text-on-surface-variant/80">
                                        {{ $m->moved_at ? $m->moved_at->format('d M Y H:i') : '-' }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-on-surface-variant/80">
                                        {{ $m->interviewer_notes ?: '-' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-xs text-on-surface-variant/50">
                                        Belum ada riwayat pergerakan tahap seleksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Conditional Bottom Sections based on stage requirements -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Scorecard Section -->
        @if($candidate->currentStage->butuh_scorecard)
            <div class="bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-title-md font-headline-lg text-on-surface mb-1">Scorecard Evaluasi</h3>
                        <p class="text-body-md text-xs text-on-surface-variant/70">Kriteria penilaian wawancara pada stage: <span class="font-bold text-primary">{{ $candidate->currentStage->nama }}</span></p>
                    </div>
                    <a href="{{ route('ats.candidate.scorecard', ['candidateId' => $candidate->id, 'stageId' => $candidate->current_stage_id]) }}" 
                       class="inline-flex items-center justify-center gap-2 px-4 h-10 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all text-xs active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.15)]">
                        <span class="material-symbols-outlined text-[16px]">rate_review</span>
                        <span>{{ $scorecards->isEmpty() ? 'Isi Scorecard' : 'Ubah Scorecard' }}</span>
                    </a>
                </div>

                @if($scorecards->isNotEmpty())
                    <div class="overflow-hidden border border-surface-container rounded-md">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-surface-container-high bg-surface-container-low/40">
                                    <th class="px-4 py-3 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant">Kriteria</th>
                                    <th class="px-4 py-3 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant text-center">Bobot</th>
                                    <th class="px-4 py-3 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant text-center">Nilai (1-10)</th>
                                    <th class="px-4 py-3 font-bold text-[11px] uppercase tracking-wider text-on-surface-variant text-right">Nilai Berbobot</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-container/30">
                                @foreach($scorecards as $s)
                                    <tr>
                                        <td class="px-4 py-3 text-sm text-on-surface font-semibold">{{ $s->kriteria }}</td>
                                        <td class="px-4 py-3 text-sm text-center text-on-surface-variant/80">{{ $s->bobot }}%</td>
                                        <td class="px-4 py-3 text-sm text-center text-on-surface font-bold text-primary">{{ $s->nilai }}</td>
                                        <td class="px-4 py-3 text-sm text-right text-on-surface-variant/80 font-bold">
                                            {{ number_format(($s->bobot * $s->nilai) / 100, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-surface-container-low/40 font-bold border-t border-surface-container-high">
                                    <td colspan="3" class="px-4 py-3 text-sm text-on-surface text-right">Total Nilai Rata-rata Berbobot:</td>
                                    <td class="px-4 py-3 text-sm text-right text-primary text-base font-headline-lg">
                                        {{ $totalWeightedScore }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center bg-surface-container-low/20 border border-dashed rounded-md text-on-surface-variant/50">
                        <span class="material-symbols-outlined text-[36px] mb-2">rate_review</span>
                        <p class="text-sm font-semibold mb-1">Scorecard belum diisi</p>
                        <p class="text-xs">Wajib mengisi scorecard sebelum melaju ke tahap berikutnya.</p>
                    </div>
                @endif
            </div>
        @endif

        <!-- Schedule Section -->
        @if($candidate->currentStage->butuh_jadwal)
            <div class="bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h3 class="text-title-md font-headline-lg text-on-surface mb-1">Jadwal Interview</h3>
                        <p class="text-body-md text-xs text-on-surface-variant/70">Pengaturan jadwal wawancara untuk stage: <span class="font-bold text-primary">{{ $candidate->currentStage->nama }}</span></p>
                    </div>
                    <a href="{{ route('ats.candidate.schedule', ['candidateId' => $candidate->id, 'stageId' => $candidate->current_stage_id]) }}" 
                       class="inline-flex items-center justify-center gap-2 px-4 h-10 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all text-xs active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.15)]">
                        <span class="material-symbols-outlined text-[16px]">calendar_today</span>
                        <span>{{ $schedules->isEmpty() ? 'Atur Jadwal' : 'Ubah Jadwal' }}</span>
                    </a>
                </div>

                @if($schedules->isNotEmpty())
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @foreach($schedules as $sched)
                            <div class="bg-surface-container-low/40 p-4 rounded-md border border-surface-container flex flex-col gap-3 relative">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                                        <span class="material-symbols-outlined text-[18px]">event</span>
                                    </div>
                                    <div>
                                        <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/65">Tanggal &amp; Waktu</span>
                                        <span class="text-sm font-bold text-on-surface">
                                            {{ $sched->tanggal ? $sched->tanggal->format('d M Y') : '-' }} @ {{ substr($sched->waktu, 0, 5) }}
                                        </span>
                                    </div>
                                </div>

                                @if($sched->tempat)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-secondary-fixed text-on-secondary-fixed flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[18px]">location_on</span>
                                        </div>
                                        <div>
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/65">Lokasi / Ruangan</span>
                                            <span class="text-sm font-semibold text-on-surface">{{ $sched->tempat }}</span>
                                        </div>
                                    </div>
                                @endif

                                @if($sched->tautan_virtual)
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-500/10 text-blue-600 flex items-center justify-center">
                                            <span class="material-symbols-outlined text-[18px]">videocam</span>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/65">Virtual Meeting Link</span>
                                            <a href="{{ $sched->tautan_virtual }}" target="_blank" class="text-xs font-semibold text-primary hover:underline block truncate">
                                                {{ $sched->tautan_virtual }}
                                            </a>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center bg-surface-container-low/20 border border-dashed rounded-md text-on-surface-variant/50">
                        <span class="material-symbols-outlined text-[36px] mb-2">calendar_today</span>
                        <p class="text-sm font-semibold mb-1">Jadwal interview belum diatur</p>
                        <p class="text-xs">Wajib menjadwalkan interview sebelum melaju ke tahap berikutnya.</p>
                    </div>
                @endif
            </div>
        @endif

    </div>
</div>
