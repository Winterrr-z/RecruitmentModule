<div>
    <x-breadcrumb :items="[['label' => 'ATS', 'url' => null], ['label' => $backLabel, 'url' => $backUrl], ['label' => $candidate->name ?? 'Detail Kandidat', 'url' => null]]" />
    <x-toast-alert />

    <!-- Content Header & Back button -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div class="flex items-center gap-4">
            <a href="{{ $backUrl }}" class="p-2 hover:bg-surface-container rounded-md transition-colors text-on-surface-variant flex items-center" title="Kembali">
                <span class="material-symbols-outlined">arrow_back</span>
            </a>
            <div>
                <h2 class="font-headline-lg text-headline-lg text-on-surface">Detail Profil Pelamar</h2>
                <p class="font-body-md text-body-md text-on-surface-variant/70">Manajemen profil, jadwal interview, dan scorecard evaluasi pelamar</p>
            </div>
        </div>

        @if (($candidate->currentStage->id === 2 || strtolower($candidate->currentStage->name) === 'final') && $candidate->status->value === 'Offered' && $candidate->vacancy && $candidate->vacancy->quota > 0)
            <a href="{{ route('ats.offering.send', ['candidateId' => $candidate->id]) }}?from={{ $backLabel === 'All Candidates' ? 'candidates' : 'dashboard' }}" 
               class="inline-flex items-center justify-center gap-2 px-5 h-11 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)] text-sm">
                <span class="material-symbols-outlined text-[20px]">mail</span>
                <span>Kirim Offering Letter</span>
            </a>
        @endif
    </div>

    <!-- Main Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start mb-8">
        
        <!-- Left Column: Profile Card & Application History -->
        <div class="flex flex-col gap-6">
            <!-- Profile Card -->
            <div class="bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
                <div class="flex flex-col items-center text-center pb-6 border-b border-surface-container-high/40">
                    <div class="w-20 h-20 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-2xl mb-4">
                        {{ strtoupper(substr($candidate->name, 0, 2)) }}
                    </div>
                    <h3 class="text-title-md font-headline-lg text-on-surface mb-1">{{ $candidate->name }}</h3>
                    <p class="text-label-sm font-label-sm text-on-surface-variant/70 mb-3">ID: #{{ $candidate->id }}</p>
                    
                    <!-- Status & Stage badge -->
                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-xs font-bold">
                            {{ $candidate->currentStage->name }}
                        </span>
                        @switch($candidate->status->value)
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
                            @case('Withdrawn')
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-500/10 text-red-700 border border-red-500/20 rounded-full text-xs font-bold">
                                    Withdrawn
                                </span>
                                @break
                            @case('Expired')
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-red-500/10 text-red-700 border border-red-500/20 rounded-full text-xs font-bold">
                                    Expired
                                </span>
                                @break
                            @default
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-surface-container-high text-on-surface-variant border border-surface-container rounded-full text-xs font-bold">
                                    {{ $candidate->status->value ?? $candidate->status }}
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
                        <span class="text-body-md text-on-surface font-semibold">{{ $candidate->phone }}</span>
                    </div>
                    <div>
                        <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Lowongan Pekerjaan</span>
                        <span class="text-body-md text-primary font-bold">{{ $candidate->vacancy?->job_title ?: 'Kandidat Mandiri' }}</span>
                        <div class="text-[11px] text-on-surface-variant/60">{{ $candidate->vacancy?->department ?: 'Tanpa Lowongan' }}</div>
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

        </div>
        
        <!-- Right Column: Unified Application Accordions -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <div>
                <h3 class="text-title-md font-headline-lg text-on-surface mb-1">Riwayat Lamaran & Pergerakan Tahap</h3>
                <p class="text-body-md text-xs text-on-surface-variant/70">Klik pada lamaran untuk membuka rincian tahapan pergerakan dan catatan wawancara.</p>
            </div>

            <div class="flex flex-col gap-4">
                @foreach($applicationHistory as $history)
                    @php
                        $isExpanded = in_array($history->id, $expandedApplications);
                        $isActiveApp = $history->id === $candidate->id;
                    @endphp
                    <div wire:key="app-history-{{ $history->id }}" class="border {{ $isActiveApp ? 'border-primary bg-primary/[0.01]' : 'border-surface-container hover:border-surface-container-high bg-surface-container-lowest' }} rounded-md shadow-[0_2px_4px_rgba(0,0,0,0.02)] overflow-hidden transition-all">
                        <!-- Accordion Header -->
                        <button type="button" wire:click="toggleApplication({{ $history->id }})" 
                                class="w-full flex items-center justify-between px-5 py-4 {{ $isActiveApp ? 'bg-primary/5 hover:bg-primary/10' : 'bg-surface-container-low/40 hover:bg-surface-container-low/80' }} transition-colors text-left">
                            <div class="flex flex-col gap-1 pr-4">
                                <div class="flex items-center flex-wrap gap-2">
                                    <span class="text-sm font-bold text-on-surface">
                                        {{ $history->vacancy?->job_title ?: 'Kandidat Mandiri' }}
                                    </span>
                                    @if($isActiveApp)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-primary text-white">Lamaran Aktif</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider bg-surface-container-high text-on-surface-variant/80">Arsip</span>
                                    @endif
                                </div>
                                <div class="flex items-center gap-4 text-xs text-on-surface-variant/65">
                                    <span>Diajukan: <strong>{{ $history->created_at->format('d M Y') }}</strong></span>
                                    <span>•</span>
                                    <span>Tahap: <strong>{{ $history->currentStage?->name ?? 'Applied' }}</strong> ({{ $history->status->value }})</span>
                                </div>
                            </div>
                            <span class="material-symbols-outlined text-on-surface-variant/70 transition-transform duration-200 {{ $isExpanded ? 'rotate-180' : '' }}">
                                expand_more
                            </span>
                        </button>

                        <!-- Accordion Content -->
                        @if($isExpanded)
                            <div class="p-5 border-t {{ $isActiveApp ? 'border-primary/20 bg-white' : 'border-surface-container/40' }} space-y-4">
                                <div class="flex flex-col gap-2">
                                    <span class="text-xs font-bold uppercase tracking-wider text-on-surface-variant/70">Alur Pergerakan Tahap</span>
                                    <div class="overflow-hidden border border-surface-container rounded-md">
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-left border-collapse">
                                                <thead>
                                                    <tr class="border-b border-surface-container bg-surface-container-low/30">
                                                        <th class="px-4 py-3 font-bold text-[10px] uppercase tracking-wider text-on-surface-variant w-32">Dari Stage</th>
                                                        <th class="px-4 py-3 font-bold text-[10px] uppercase tracking-wider text-on-surface-variant w-32">Ke Stage</th>
                                                        <th class="px-4 py-3 font-bold text-[10px] uppercase tracking-wider text-on-surface-variant w-40">Tanggal</th>
                                                        <th class="px-4 py-3 font-bold text-[10px] uppercase tracking-wider text-on-surface-variant">Catatan</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-surface-container/20">
                                                    @forelse($history->candidateMovements as $m)
                                                        <tr wire:key="movement-row-{{ $m->id }}" class="hover:bg-surface-container-low/10 transition-colors">
                                                            <td class="px-4 py-2.5 text-xs text-on-surface font-semibold">
                                                                {{ $m->fromStage->name }}
                                                            </td>
                                                            <td class="px-4 py-2.5 text-xs text-primary font-bold">
                                                                {{ $m->toStage->name }}
                                                            </td>
                                                            <td class="px-4 py-2.5 text-xs text-on-surface-variant/80">
                                                                {{ $m->moved_at ? $m->moved_at->format('d M Y H:i') : '-' }}
                                                            </td>
                                                            <td class="px-4 py-2.5 text-xs text-on-surface-variant/80 min-w-[200px]">
                                                                @php
                                                                    $isPassed = !$isActiveApp || $m->to_stage_id !== $candidate->current_stage_id || !in_array($candidate->status->value, ['Applied', 'In Progress', 'Offered']);
                                                                @endphp
                                                                
                                                                @if($isPassed)
                                                                    <div class="w-full text-[11px] p-2 bg-surface-container-low border border-surface-container-high/40 text-on-surface-variant/75 rounded-sm min-h-[34px] cursor-not-allowed">
                                                                        {{ $m->interviewer_notes ?: 'Tidak ada catatan.' }}
                                                                    </div>
                                                                @else
                                                                    <form wire:submit.prevent="saveNote({{ $m->id }})" class="flex items-start gap-1.5">
                                                                        <textarea wire:model="notes.{{ $m->id }}" rows="2" 
                                                                                  class="w-full text-[11px] p-2 rounded-sm bg-surface border border-surface-container-high focus:border-primary/55 focus:ring-1 focus:ring-primary/20 transition-all placeholder:text-on-surface-variant/40" 
                                                                                  placeholder="Tulis catatan untuk stage ini..."></textarea>
                                                                        <button type="submit" class="p-1.5 rounded-sm bg-primary/10 text-primary hover:bg-primary hover:text-white transition-colors mt-0.5" title="Simpan Catatan">
                                                                            <span class="material-symbols-outlined text-[15px]">save</span>
                                                                        </button>
                                                                    </form>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="4" class="px-4 py-6 text-center text-xs text-on-surface-variant/50">
                                                                Belum ada riwayat pergerakan tahap seleksi pada lamaran ini.
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    <!-- Unified Bottom Sections: Scorecard & Schedule Histories -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
        
        <!-- Scorecard History Column -->
        <div class="bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
            <div>
                <h3 class="text-title-md font-headline-lg text-on-surface mb-1">Riwayat Scorecard</h3>
                <p class="text-body-md text-xs text-on-surface-variant/70">Kumpulan riwayat penilaian scorecard dari seluruh lamaran pekerjaan pelamar ini</p>
            </div>

            @php
                $hasAnyScorecards = $applicationHistory->contains(fn($h) => $h->scorecards->isNotEmpty() || ($h->id === $candidate->id && $h->currentStage->needs_scorecard));
            @endphp

            @if($hasAnyScorecards)
                <div class="flex flex-col gap-3">
                    @foreach($applicationHistory as $history)
                        @php
                            $isCurrentApp = $history->id === $candidate->id;
                            $needsScorecard = $history->currentStage->needs_scorecard;
                            $hasScorecards = $history->scorecards->isNotEmpty();
                            $isScorecardExpanded = in_array($history->id, $expandedScorecards);
                        @endphp
                        @if($hasScorecards || ($isCurrentApp && $needsScorecard))
                            <div wire:key="scorecard-block-{{ $history->id }}" class="border {{ $isCurrentApp ? 'border-primary bg-primary/[0.01]' : 'border-surface-container hover:border-surface-container-high bg-surface-container-lowest' }} rounded-md shadow-sm overflow-hidden transition-all">
                                <!-- Accordion Header -->
                                <button type="button" wire:click="toggleScorecard({{ $history->id }})" 
                                        class="w-full flex items-center justify-between px-4 py-3 {{ $isCurrentApp ? 'bg-primary/5 hover:bg-primary/10' : 'bg-surface-container-low/40 hover:bg-surface-container-low/80' }} transition-colors text-left">
                                    <div class="flex flex-col gap-0.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold text-on-surface">
                                                {{ $history->vacancy?->job_title ?: 'Kandidat Mandiri' }}
                                            </span>
                                            @if($isCurrentApp)
                                                <span class="text-[8px] font-bold uppercase tracking-wider bg-primary text-white px-1.5 py-0.5 rounded-full">Aktif</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-on-surface-variant/65">Diajukan: {{ $history->created_at->format('d M Y') }}</span>
                                    </div>
                                    <span class="material-symbols-outlined text-on-surface-variant/70 text-[18px] transition-transform duration-200 {{ $isScorecardExpanded ? 'rotate-180' : '' }}">
                                        expand_more
                                    </span>
                                </button>

                                <!-- Accordion Content -->
                                @if($isScorecardExpanded)
                                    <div class="p-4 border-t {{ $isCurrentApp ? 'border-primary/20 bg-white' : 'border-surface-container/40' }} space-y-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/60">Detail Evaluasi</span>
                                                @if($isCurrentApp && $needsScorecard)
                                                    <div style="display: none;">Scorecard Evaluasi</div>
                                                @endif
                                            </div>
                                            @if($isCurrentApp && $needsScorecard)
                                                <a href="{{ route('ats.candidate.scorecard', ['candidateId' => $candidate->id, 'stageId' => $candidate->current_stage_id]) }}" 
                                                   class="inline-flex items-center justify-center gap-1.5 px-3 h-8 bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all text-xs font-bold rounded-md">
                                                    <span class="material-symbols-outlined text-[14px]">rate_review</span>
                                                    <span>{{ $history->scorecards->isEmpty() ? 'Isi Scorecard' : 'Ubah Scorecard' }}</span>
                                                </a>
                                            @endif
                                        </div>

                                        @if($hasScorecards)
                                            @php
                                                $groupedScorecards = $history->scorecards->groupBy('stage_id');
                                            @endphp
                                            <div class="flex flex-col gap-4">
                                                @foreach($groupedScorecards as $stageId => $sList)
                                                    @php
                                                        $stageName = $sList->first()->stage?->name ?? 'Tahap';
                                                        $sumWeighted = $sList->sum(fn($s) => $s->weight * $s->score);
                                                        $weightedAvg = round($sumWeighted / 100, 2);
                                                    @endphp
                                                    <div class="overflow-hidden border border-surface-container rounded-md">
                                                        <div class="px-4 py-2 bg-surface-container-low/40 border-b border-surface-container text-[11px] font-bold text-on-surface-variant">
                                                            Tahap: {{ $stageName }}
                                                        </div>
                                                        <table class="w-full text-left border-collapse text-[11px]">
                                                            <thead>
                                                                <tr class="border-b border-surface-container bg-surface-container-low/20">
                                                                    <th class="px-3 py-2 font-bold text-[9px] uppercase tracking-wider text-on-surface-variant/80">Kriteria</th>
                                                                    <th class="px-3 py-2 font-bold text-[9px] uppercase tracking-wider text-on-surface-variant/80 text-center w-16">Bobot</th>
                                                                    <th class="px-3 py-2 font-bold text-[9px] uppercase tracking-wider text-on-surface-variant/80 text-center w-16">Nilai</th>
                                                                    <th class="px-3 py-2 font-bold text-[9px] uppercase tracking-wider text-on-surface-variant/80 text-right w-24">Nilai Berbobot</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody class="divide-y divide-surface-container/20">
                                                                @foreach($sList as $s)
                                                                    <tr>
                                                                        <td class="px-3 py-1.5 text-on-surface font-semibold">{{ $s->criteria }}</td>
                                                                        <td class="px-3 py-1.5 text-center text-on-surface-variant/80">{{ $s->weight }}%</td>
                                                                        <td class="px-3 py-1.5 text-center font-bold text-primary">{{ $s->score }}</td>
                                                                        <td class="px-3 py-1.5 text-right text-on-surface-variant/80 font-bold">
                                                                            {{ number_format(($s->weight * $s->score) / 100, 2) }}
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                            <tfoot>
                                                                <tr class="bg-surface-container-low/10 font-bold border-t border-surface-container">
                                                                    <td colspan="3" class="px-3 py-1.5 text-[10px] text-on-surface text-right">Rata-rata Berbobot:</td>
                                                                    <td class="px-3 py-1.5 text-[10px] text-right text-primary font-bold">
                                                                        {{ $weightedAvg }}
                                                                    </td>
                                                                </tr>
                                                            </tfoot>
                                                        </table>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center py-5 text-center bg-surface-container-low/20 border border-dashed rounded-md text-on-surface-variant/50">
                                                <span class="material-symbols-outlined text-[24px] mb-1">rate_review</span>
                                                <p class="text-xs font-semibold">Scorecard belum diisi</p>
                                                <p class="text-[10px] px-4">Wajib mengisi scorecard sebelum kandidat melaju ke tahap berikutnya.</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center bg-surface-container-low/20 border border-dashed rounded-md text-on-surface-variant/50">
                    <span class="material-symbols-outlined text-[36px] mb-2">rate_review</span>
                    <p class="text-sm font-semibold mb-1">Belum ada scorecard evaluasi</p>
                    <p class="text-xs">Tahapan seleksi pelamar ini belum atau tidak membutuhkan scorecard.</p>
                </div>
            @endif
        </div>

        <!-- Schedule History Column -->
        <div class="bg-surface-container-lowest p-6 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
            <div>
                <h3 class="text-title-md font-headline-lg text-on-surface mb-1">Riwayat Jadwal</h3>
                <p class="text-body-md text-xs text-on-surface-variant/70">Daftar riwayat jadwal interview pelamar dari seluruh lamaran pekerjaan pelamar ini</p>
            </div>

            @php
                $hasAnySchedules = $applicationHistory->contains(fn($h) => $h->interviewSchedules->isNotEmpty() || ($h->id === $candidate->id && $h->currentStage->needs_schedule));
            @endphp

            @if($hasAnySchedules)
                <div class="flex flex-col gap-3">
                    @foreach($applicationHistory as $history)
                        @php
                            $isCurrentApp = $history->id === $candidate->id;
                            $needsSchedule = $history->currentStage->needs_schedule;
                            $hasSchedules = $history->interviewSchedules->isNotEmpty();
                            $isScheduleExpanded = in_array($history->id, $expandedSchedules);
                        @endphp
                        @if($hasSchedules || ($isCurrentApp && $needsSchedule))
                            <div wire:key="schedule-block-{{ $history->id }}" class="border {{ $isCurrentApp ? 'border-primary bg-primary/[0.01]' : 'border-surface-container hover:border-surface-container-high bg-surface-container-lowest' }} rounded-md shadow-sm overflow-hidden transition-all">
                                <!-- Accordion Header -->
                                <button type="button" wire:click="toggleSchedule({{ $history->id }})" 
                                        class="w-full flex items-center justify-between px-4 py-3 {{ $isCurrentApp ? 'bg-primary/5 hover:bg-primary/10' : 'bg-surface-container-low/40 hover:bg-surface-container-low/80' }} transition-colors text-left">
                                    <div class="flex flex-col gap-0.5">
                                        <div class="flex items-center gap-1.5 flex-wrap">
                                            <span class="text-xs font-bold text-on-surface">
                                                {{ $history->vacancy?->job_title ?: 'Kandidat Mandiri' }}
                                            </span>
                                            @if($isCurrentApp)
                                                <span class="text-[8px] font-bold uppercase tracking-wider bg-primary text-white px-1.5 py-0.5 rounded-full">Aktif</span>
                                            @endif
                                        </div>
                                        <span class="text-[10px] text-on-surface-variant/65">Diajukan: {{ $history->created_at->format('d M Y') }}</span>
                                    </div>
                                    <span class="material-symbols-outlined text-on-surface-variant/70 text-[18px] transition-transform duration-200 {{ $isScheduleExpanded ? 'rotate-180' : '' }}">
                                        expand_more
                                    </span>
                                </button>

                                <!-- Accordion Content -->
                                @if($isScheduleExpanded)
                                    <div class="p-4 border-t {{ $isCurrentApp ? 'border-primary/20 bg-white' : 'border-surface-container/40' }} space-y-4">
                                        <div class="flex items-center justify-between gap-2">
                                            <div class="flex flex-col">
                                                <span class="text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/60">Detail Wawancara</span>
                                                @if($isCurrentApp && $needsSchedule)
                                                    <div style="display: none;">Jadwal Interview</div>
                                                @endif
                                            </div>
                                            @if($isCurrentApp && $needsSchedule)
                                                <a href="{{ route('ats.candidate.schedule', ['candidateId' => $candidate->id, 'stageId' => $candidate->current_stage_id]) }}" 
                                                   class="inline-flex items-center justify-center gap-1.5 px-3 h-8 bg-primary/10 text-primary hover:bg-primary hover:text-white transition-all text-xs font-bold rounded-md">
                                                    <span class="material-symbols-outlined text-[14px]">calendar_today</span>
                                                    <span>{{ $history->interviewSchedules->isEmpty() ? 'Atur Jadwal' : 'Ubah Jadwal' }}</span>
                                                </a>
                                            @endif
                                        </div>

                                        @if($hasSchedules)
                                            @php
                                                $groupedSchedules = $history->interviewSchedules->groupBy('stage_id');
                                            @endphp
                                            <div class="flex flex-col gap-4">
                                                @foreach($groupedSchedules as $stageId => $schedList)
                                                    @php
                                                        $stageName = $schedList->first()->stage?->name ?? 'Tahap';
                                                    @endphp
                                                    <div class="flex flex-col gap-2 p-3 bg-surface-container-low/30 border border-surface-container rounded-md">
                                                        <div class="text-[11px] font-bold text-on-surface-variant pb-1 border-b border-surface-container/40">
                                                            Tahap: {{ $stageName }}
                                                        </div>
                                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                                                            @foreach($schedList as $sched)
                                                                <div class="flex flex-col gap-1 text-[11px]">
                                                                    <div class="flex items-center gap-1.5 text-on-surface-variant">
                                                                        <span class="material-symbols-outlined text-[13px] text-primary">event</span>
                                                                        <span class="font-semibold text-on-surface">{{ $sched->date ? $sched->date->format('d M Y') : '-' }} @ {{ substr($sched->time, 0, 5) }}</span>
                                                                    </div>
                                                                    @if($sched->venue)
                                                                        <div class="flex items-center gap-1.5 text-on-surface-variant">
                                                                            <span class="material-symbols-outlined text-[13px] text-secondary">location_on</span>
                                                                            <span>{{ $sched->venue }}</span>
                                                                        </div>
                                                                    @endif
                                                                    @if($sched->virtual_link)
                                                                        <div class="flex items-center gap-1.5">
                                                                            <span class="material-symbols-outlined text-[13px] text-blue-500">videocam</span>
                                                                            <a href="{{ $sched->virtual_link }}" target="_blank" class="text-primary hover:underline truncate w-full max-w-[130px]">
                                                                                {{ $sched->virtual_link }}
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="flex flex-col items-center justify-center py-5 text-center bg-surface-container-low/20 border border-dashed rounded-md text-on-surface-variant/50">
                                                <span class="material-symbols-outlined text-[24px] mb-1">calendar_today</span>
                                                <p class="text-xs font-semibold">Jadwal interview belum diatur</p>
                                                <p class="text-[10px] px-4">Wajib menjadwalkan interview sebelum kandidat melaju ke tahap berikutnya.</p>
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-center bg-surface-container-low/20 border border-dashed rounded-md text-on-surface-variant/50">
                    <span class="material-symbols-outlined text-[36px] mb-2">calendar_today</span>
                    <p class="text-sm font-semibold mb-1">Belum ada jadwal interview</p>
                    <p class="text-xs">Tahapan seleksi pelamar ini belum atau tidak membutuhkan jadwal wawancara.</p>
                </div>
            @endif
        </div>

    </div>
</div>