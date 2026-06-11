<div>
    <x-breadcrumb :items="[['label' => 'Manpower Planning', 'url' => route('mpp.index')], ['label' => $mpp->plan_name ?? 'Detail MPP', 'url' => null]]" />
    <!-- Main Detail Content -->
    <div class="px-gutter py-8 max-w-screen-xl mx-auto space-y-8">
        <x-toast-alert />

        <!-- Hero Title Section -->
        <section class="space-y-4">
            <div class="flex items-center gap-4">
                <h1 class="font-headline-lg text-headline-lg text-on-surface tracking-tight">{{ $mpp->plan_name }}</h1>
                @php $computedStatus = $mpp->getComputedStatus(); @endphp
                @if($computedStatus && !in_array($computedStatus, ['Closed', 'Completed']))
                    <x-mpp-status-badge :status="$computedStatus" class="self-start sm:self-auto" />
                @endif
            </div>
        </section>

        <!-- Step Indicator -->
        <section class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.06)] border border-surface-container/30">
            @php
                $isCompleted = in_array($mpp->status, [\App\Enums\MppStatus::COMPLETED, \App\Enums\MppStatus::CLOSED]) || $computedStatus === 'Completed';
                
                $isDraftActive = !$isCompleted;
                $isApprovedActive = (($mpp->status instanceof \App\Enums\MppStatus ? $mpp->status->value : $mpp->status) === 'Approved' || $hasVacancy) && !$isCompleted;
                $isVacancyActive = $hasVacancy && !$isCompleted;
                
                $slaDisplay = $mpp->sla_days >= 30 ? (int) floor($mpp->sla_days / 30) . ' Bulan' : (int) $mpp->sla_days . ' Hari';
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
                        @if($isVacancyActive)
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
                <div class="hidden md:block flex-1 h-0.5 transition-all duration-300 {{ $isVacancyActive ? 'bg-primary' : 'bg-surface-container-high' }}"></div>

                <!-- Step 3: Vacancy Dibuat -->
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 {{ $isVacancyActive ? 'bg-primary text-white ring-4 ring-primary/20' : 'bg-surface-container-high text-on-surface-variant' }}">
                        3
                    </div>
                    <div>
                        <p class="text-label-sm font-label-sm font-bold {{ $isVacancyActive ? 'text-primary' : 'text-on-surface-variant/70' }}">Lowongan Kerja Dibuat</p>
                        <p class="text-xs text-on-surface-variant/70">Proses recruitment aktif</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Urgency Progress Section -->
        <section class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.06)] border border-surface-container/30 space-y-6">
            @php
                $now = now();
                $target = \Carbon\Carbon::parse($mpp->absolute_target_date);
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
                        $sisaWaktuFormatted = (int) floor($daysRemaining / 30) . ' Bulan';
                    } else {
                        $sisaWaktuFormatted = (int) floor($daysRemaining) . ' Hari';
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
                        <p class="font-title-md text-title-md font-bold">0 / {{ $mpp->quota }} Orang</p>
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
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->department }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Jabatan</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->job_title }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Kuota Dibutuhkan</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $mpp->quota }} Orang</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Estimasi Gaji</p>
                        <p class="font-body-lg text-body-lg font-semibold text-primary">
                            @if($mpp->estimated_salary_min && $mpp->estimated_salary_max)
                                Rp {{ str_replace(',', '.', $mpp->estimated_salary_min) }} - Rp {{ str_replace(',', '.', $mpp->estimated_salary_max) }}
                            @elseif($mpp->estimated_salary_min)
                                Rp {{ str_replace(',', '.', $mpp->estimated_salary_min) }}
                            @elseif($mpp->estimated_salary_max)
                                Rp {{ str_replace(',', '.', $mpp->estimated_salary_max) }}
                            @else
                                -
                            @endif
                        </p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">SLA Perencanaan</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ $slaDisplay }}</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-label-sm font-label-sm text-on-surface-variant">Target Selesai</p>
                        <p class="font-body-lg text-body-lg font-semibold text-on-surface">{{ \Carbon\Carbon::parse($mpp->absolute_target_date)->translatedFormat('d F Y') }}</p>
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

            <!-- Recruitment Requests Terkait -->
            @if($hasVacancy)
                <div class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.04)] border border-surface-container/30 space-y-6">
                    <h4 class="font-title-md text-title-md flex items-center gap-2 text-on-surface">
                        <span class="material-symbols-outlined text-primary">assignment_ind</span>
                        Recruitment Request Terkait
                    </h4>
                    
                    <div class="overflow-x-auto border border-surface-container rounded-md">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-container-low border-b border-surface-container text-label-sm font-label-sm text-on-surface-variant">
                                    <th class="p-4 pl-6">Posisi / Jabatan</th>
                                    <th class="p-4">Tipe & Lokasi</th>
                                    <th class="p-4 text-center">Kuota</th>
                                    <th class="p-4 text-center">Hired / Pelamar</th>
                                    <th class="p-4">Status</th>
                                    <th class="p-4 pr-6 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-surface-container">
                                @foreach($mppVacancies as $l)
                                    @php
                                        $hired = $l->candidates()->where('candidates.status', 'Hired')->count();
                                        $total = $l->candidates()->count();
                                    @endphp
                                    <tr class="hover:bg-surface-container-low/50 transition-colors text-body-md text-on-surface">
                                        <td class="p-4 pl-6 font-semibold">
                                            {{ $l->job_title }}
                                            <div class="text-xs text-on-surface-variant font-normal">{{ $l->department }}</div>
                                        </td>
                                        <td class="p-4">
                                            <div class="flex items-center gap-1.5 text-xs text-on-surface-variant font-normal">
                                                <span class="capitalize">{{ $l->employment_type }}</span>
                                                <span>•</span>
                                                <span class="capitalize">{{ $l->location }}</span>
                                            </div>
                                        </td>
                                        <td class="p-4 text-center font-semibold">{{ $l->quota }} Orang</td>
                                        <td class="p-4 text-center font-semibold">
                                            <span class="text-primary">{{ $hired }}</span>
                                            <span class="text-on-surface-variant">/ {{ $total }}</span>
                                        </td>
                                        <td class="p-4">
                                            @php
                                                $lStatus = $l->status instanceof \App\Enums\RrStatus ? $l->status->value : $l->status;
                                            @endphp
                                            @if($lStatus === 'Published')
                                                <span class="px-2.5 py-0.5 bg-[#dcfce7] text-[#166534] text-xs font-bold rounded-full uppercase">Aktif (Published)</span>
                                            @elseif(in_array($lStatus, ['Completed', 'Closed']))
                                                <span class="px-2.5 py-0.5 bg-[#f3f4f6] text-[#374151] text-xs font-bold rounded-full uppercase">Closed</span>
                                            @elseif($lStatus === 'Completed')
                                                <span class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-bold rounded-full uppercase">Completed</span>
                                            @elseif($lStatus === 'Ready to Publish')
                                                <span class="px-2.5 py-0.5 bg-secondary-fixed text-on-secondary-fixed-variant text-xs font-bold rounded-full uppercase">Ready to Publish</span>
                                            @else
                                                <span class="px-2.5 py-0.5 bg-[#fef9c3] text-[#854d0e] text-xs font-bold rounded-full uppercase">Draft</span>
                                            @endif
                                        </td>
                                        <td class="p-4 pr-6 text-right">
                                            <a href="{{ route('rr.show', $l->id) }}" class="inline-flex items-center gap-1 text-primary hover:text-primary-container font-bold transition-all text-sm">
                                                <span>Detail</span>
                                                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </section>

        <!-- Bottom Reactive Action Area -->
        <section class="sticky bottom-8 left-0 right-0 z-40">
            <div class="bg-surface-container-lowest/80 backdrop-blur-xl border border-surface-container/50 p-6 rounded-md shadow-[0px_32px_64px_-16px_rgba(0,0,0,0.12)] flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-4">
                    <a class="group flex items-center gap-2 text-primary font-bold no-underline transition-all" href="{{ route('mpp.index') }}">
                        <span class="material-symbols-outlined no-underline">arrow_back</span>
                        <span class="font-label-sm text-label-sm group-hover:underline">Kembali ke Manpower Planning</span>
                    </a>
                </div>
                <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                    <!-- Edit Button -->
                    @if($computedStatus === 'Closed' || $computedStatus === 'Filled')
                        {{-- Edit disabled/hidden if closed or filled --}}
                    @elseif(($mpp->status instanceof \App\Enums\MppStatus ? $mpp->status->value : $mpp->status) === 'Approved' && $mpp->hasPublishedRr())
                        {{-- Edit disabled if plan is approved and RR is published --}}
                    @else
                        <a href="{{ route('mpp.edit', $mpp->id) }}" class="px-6 h-14 bg-surface-container-low text-on-surface-variant hover:bg-surface-container border border-surface-container font-bold rounded-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                            <span>Edit Plan</span>
                        </a>
                    @endif
                    
                    <!-- Tutup Plan Button -->
                    @if(in_array(($mpp->status instanceof \App\Enums\MppStatus ? $mpp->status->value : $mpp->status), ['Approved', 'Draft']) && !$hasActiveRr)
                        <button wire:click="closePlan" wire:confirm="Anda yakin ingin menutup Plan ini?" class="px-6 h-14 bg-error text-white hover:brightness-110 border border-error font-bold rounded-md transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">cancel</span>
                            <span>Tutup Plan</span>
                        </button>
                    @endif

                    <!-- Approve Button -->
                    @if(($mpp->status instanceof \App\Enums\MppStatus ? $mpp->status->value : $mpp->status) === 'Draft')
                        <button wire:click="approve" wire:confirm="Approve MPP ini?" class="px-8 h-14 bg-[#10b981] text-white font-bold rounded-md shadow-[0px_8px_16px_-4px_rgba(16,185,129,0.3)] hover:brightness-110 transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">check_circle</span>
                            <span>Approve Perencanaan</span>
                        </button>
                    @endif

                    <!-- Buat Rencana Rekrutmen Button -->
                    @if(($mpp->status instanceof \App\Enums\MppStatus ? $mpp->status->value : $mpp->status) === 'Approved' && $remainingQuota > 0 && !$hasActiveRr)
                        <a href="{{ route('rr.create', ['mpp_id' => $mpp->id]) }}" class="px-8 h-14 bg-primary text-white font-bold rounded-md shadow-[0px_8px_16px_-4px_rgba(107,56,212,0.3)] hover:bg-primary-container transition-all active:scale-95 flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined">add_box</span>
                            <span>Buat Rencana Rekrutmen</span>
                        </a>
                    @endif
                </div>
            </div>
        </section>
    </div>
</div>