<div>
    {{-- ===== SAPAAN ===== --}}
    <div class="mb-10">
        <h1 class="font-headline-lg text-headline-lg text-on-surface">
            Halo, <span class="text-primary">{{ auth()->user()->name }}</span>!
        </h1>
        <p class="font-body-md text-body-md text-on-surface-variant mt-1">
            Berikut adalah status perjalanan karir Anda bersama kami.
        </p>
    </div>

    {{-- ===== KANDIDAT HIRED BANNER ===== --}}
    @if($hiredApplications->isNotEmpty())
        <div class="mb-12 bg-gradient-to-r from-primary via-primary/90 to-primary-container p-8 rounded-2xl shadow-xl border border-primary/20 relative overflow-hidden">
            <!-- Decorative Elements -->
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>
            
            <div class="relative z-10 flex flex-col md:flex-row items-center gap-6">
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center shadow-lg shrink-0">
                    <span class="material-symbols-outlined text-[40px] text-primary">celebration</span>
                </div>
                <div class="text-white flex-1 text-center md:text-left">
                    <h2 class="text-2xl font-extrabold mb-2 tracking-tight">Selamat! Anda Diterima 🎉</h2>
                    <p class="text-white/80 font-medium">
                        Selamat bergabung! Lamaran Anda untuk posisi <span class="font-bold text-white">{{ $hiredApplications->first()->vacancy?->title ?: ($hiredApplications->first()->vacancy?->job_title ?? 'terkait') }}</span> telah disetujui. Silakan cek email Anda untuk instruksi onboarding selanjutnya.
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- ===== INTERVIEW HARI INI BANNER ===== --}}
    @php
        $hasInterviewToday = false;
        $todayInterviewDetails = [];
        foreach($activeApplications as $app) {
            $schedule = $app->interviewSchedules->where('stage_id', $app->current_stage_id)->first();
            if ($schedule && $schedule->date->isToday()) {
                $hasInterviewToday = true;
                $todayInterviewDetails[] = [
                    'job' => $app->vacancy?->title ?: ($app->vacancy?->job_title ?? 'Posisi'),
                    'time' => $schedule->time ? \Carbon\Carbon::parse($schedule->time)->format('H:i') : null,
                ];
            }
        }
    @endphp

    @if($hasInterviewToday)
        <div class="mb-10 bg-blue-50 border border-blue-200 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row items-center gap-5 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 opacity-10">
                <span class="material-symbols-outlined text-[120px] text-blue-600">calendar_today</span>
            </div>
            <div class="w-14 h-14 bg-blue-100 rounded-full flex items-center justify-center shrink-0 relative z-10 border border-blue-200">
                <span class="material-symbols-outlined text-[32px] text-blue-600">event_available</span>
            </div>
            <div class="flex-1 text-center md:text-left relative z-10">
                <h3 class="text-lg font-extrabold text-blue-800 mb-1.5 tracking-tight">Pengingat: Wawancara Hari Ini!</h3>
                <p class="text-sm text-blue-700 leading-relaxed">
                    Mohon persiapkan diri Anda sebaik mungkin. Anda memiliki jadwal wawancara untuk posisi: 
                    @foreach($todayInterviewDetails as $detail)
                        <span class="font-bold text-blue-900">{{ $detail['job'] }}</span> @if($detail['time']) (Pukul {{ $detail['time'] }}) @endif{{ $loop->last ? '.' : ', ' }}
                    @endforeach
                    Cek detail lokasi atau tautan pertemuan pada kartu lamaran di bawah.
                </p>
            </div>
        </div>
    @endif

    {{-- ===== LAMARAN AKTIF ===== --}}
    <section class="mb-12">
        <div class="flex items-center gap-3 mb-6">
            <span class="material-symbols-outlined text-primary text-[22px]">work</span>
            <h2 class="font-title-md text-title-md text-on-surface font-bold">Lamaran Aktif</h2>
            @if($activeApplications->isNotEmpty())
                <span class="ml-1 px-2.5 py-0.5 rounded-full bg-primary/10 text-primary text-xs font-bold">
                    {{ $activeApplications->count() }}
                </span>
            @endif
        </div>

        @if($activeApplications->isEmpty())
            {{-- Empty state --}}
            <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-xl border border-dashed border-outline-variant/50">
                <span class="material-symbols-outlined text-[48px] text-on-surface-variant/30 mb-3">inbox</span>
                <p class="font-body-md text-body-md text-on-surface-variant/60">Belum ada lamaran aktif.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($activeApplications as $candidate)
                    @php
                        $stageName = $candidate->currentStage?->name;
                        $stageIcon = $this->getStageIcon($stageName);
                    @endphp

                    <div class="min-h-[360px] bg-surface-container-lowest rounded-xl p-6 shadow-[0_30px_40px_rgba(107,56,212,0.04)] hover:shadow-[0_40px_50px_rgba(107,56,212,0.06)] transition-shadow duration-300 flex flex-col items-center justify-center gap-6 text-center max-w-sm mx-auto w-full border border-surface-container-high">
                        {{-- Info Lamaran --}}
                        <div>
                            <h3 class="font-title-md text-title-md text-on-surface font-bold">
                                {{ $candidate->vacancy?->title ?: ($candidate->vacancy?->job_title ?? 'Posisi tidak tersedia') }}
                            </h3>
                            <p class="font-body-md text-xs text-on-surface-variant mt-1">
                                Dikirim pada {{ $candidate->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>

                        {{-- Stage & Status --}}
                        <div class="flex flex-col items-center gap-3 w-full">
                            {{-- Badge tahap --}}
                            <span class="bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-xs px-4 py-1.5 rounded-full inline-flex items-center gap-1.5 font-semibold">
                                <span class="material-symbols-outlined text-[14px]">{{ $stageIcon }}</span>
                                {{ $stageName ?? 'Menunggu' }}
                            </span>

                            {{-- Lingkaran berdenyut --}}
                            <div class="flex flex-col items-center w-full">
                                <div class="w-11 h-11 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center shadow-lg ring-4 ring-primary-container/20 mb-2 relative">
                                    <span class="material-symbols-outlined text-[22px]">{{ $stageIcon }}</span>
                                    <div class="absolute inset-0 rounded-full animate-ping bg-primary-container/30"></div>
                                </div>
                                <span class="font-label-sm text-xs text-primary font-bold uppercase tracking-wider mb-2">
                                    Status Aktif
                                </span>

                                {{-- Tanggal Jadwal (jika stage butuh jadwal) --}}
                                @if($candidate->currentStage?->needs_schedule)
                                    @php
                                        $schedule = $candidate->interviewSchedules->where('stage_id', $candidate->current_stage_id)->first();
                                    @endphp
                                    @if($schedule)
                                        <div class="w-full max-w-[240px] px-3 py-2 rounded-lg bg-primary/5 border border-primary/10 text-xs text-primary flex items-center justify-center gap-1.5 font-semibold">
                                            <span class="material-symbols-outlined text-[16px] shrink-0">calendar_month</span>
                                            <span class="truncate">
                                                {{ $schedule->date->translatedFormat('d M Y') }}
                                                @if($schedule->time)
                                                    · {{ \Carbon\Carbon::parse($schedule->time)->format('H:i') }}
                                                @endif
                                            </span>
                                        </div>
                                        @if($schedule->venue)
                                            <div class="w-full max-w-[240px] mt-1.5 px-3 py-1.5 rounded-lg bg-surface-container border border-surface-container-high text-[11px] text-on-surface-variant flex items-center justify-center gap-1.5 font-semibold">
                                                <span class="material-symbols-outlined text-[14px] shrink-0">location_on</span>
                                                <span class="truncate" title="{{ $schedule->venue }}">{{ $schedule->venue }}</span>
                                            </div>
                                        @endif
                                        @if($schedule->virtual_link)
                                            <div class="w-full max-w-[240px] mt-1.5 px-3 py-1.5 rounded-lg bg-blue-50 border border-blue-100 text-[11px] text-blue-700 flex items-center justify-center gap-1.5 font-semibold">
                                                <span class="material-symbols-outlined text-[14px] shrink-0">videocam</span>
                                                <a href="{{ $schedule->virtual_link }}" target="_blank" class="truncate hover:underline" title="{{ $schedule->virtual_link }}">Gabung Rapat Virtual</a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="w-full max-w-[240px] px-3 py-2 rounded-lg bg-amber-500/5 border border-amber-500/10 text-xs text-amber-600 flex items-center justify-center gap-1.5 font-semibold">
                                            <span class="material-symbols-outlined text-[16px] shrink-0">pending_actions</span>
                                            <span>Menunggu Jadwal</span>
                                        </div>
                                    @endif
                                @endif
                            </div>
                        </div>

                        {{-- Offering Actions --}}
                        @if($candidate->offering_token && (!$candidate->offering_token_expires_at || !$candidate->offering_token_expires_at->isPast()))
                            <div class="w-full mt-4 bg-green-50 border border-green-200 rounded-md p-4 flex flex-col gap-3 shadow-sm">
                                <p class="text-sm font-extrabold text-green-700 text-center flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[20px]">stars</span>
                                    Selamat! Ada Penawaran Baru
                                </p>
                                <a href="{{ route('offering.response', ['token' => $candidate->offering_token]) }}" target="_blank" class="w-full py-3 bg-white text-primary font-bold text-sm rounded-md border border-primary/20 hover:bg-primary/5 transition-colors shadow-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">visibility</span>
                                    Lihat Surat Penawaran
                                </a>
                            </div>
                        @elseif($candidate->currentStage?->is_final_stage)
                            <div class="w-full mt-4 bg-amber-50 border border-amber-200 rounded-xl p-4 flex flex-col gap-2 shadow-sm">
                                <p class="text-xs font-bold text-amber-700 text-center flex items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-[18px]">info</span>
                                    Lamaran Sedang Diproses
                                </p>
                                <p class="text-[11px] text-amber-600/90 text-center leading-relaxed">
                                    Harap periksa email Anda atau dashboard ini secara berkala.
                                </p>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </section>

    {{-- ===== LAMARAN TIDAK AKTIF (hanya tampil jika ada) ===== --}}
    @if($inactiveApplications->isNotEmpty())
        <section>
            <div class="flex items-center gap-3 mb-6">
                <span class="material-symbols-outlined text-on-surface-variant text-[22px]">archive</span>
                <h2 class="font-title-md text-title-md text-on-surface font-bold">Riwayat Lamaran</h2>
                <span class="ml-1 px-2.5 py-0.5 rounded-full bg-surface-container-high text-on-surface-variant text-xs font-bold">
                    {{ $inactiveApplications->count() }}
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                @foreach($inactiveApplications as $candidate)
                    @php
                        // Konfigurasi tampilan per status
                        $config = match($candidate->status) {
                            \App\Enums\CandidateStatus::HIRED            => [
                                'badge'      => 'Diterima',
                                'badgeCls'   => 'bg-green-100 text-green-700',
                                'icon'       => 'check_circle',
                                'circleCls'  => 'bg-green-100 text-green-600',
                                'label'      => 'Selesai',
                                'labelCls'   => 'text-green-600',
                            ],
                            \App\Enums\CandidateStatus::REJECTED          => [
                                'badge'      => 'Tidak Lolos',
                                'badgeCls'   => 'bg-red-900/10 text-red-800 border border-red-900/20',
                                'icon'       => 'close',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                            \App\Enums\CandidateStatus::WITHDRAWN          => [
                                'badge'      => 'Mengundurkan Diri',
                                'badgeCls'   => 'bg-red-100 text-red-700',
                                'icon'       => 'cancel',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                            \App\Enums\CandidateStatus::EXPIRED          => [
                                'badge'      => 'Kedaluwarsa',
                                'badgeCls'   => 'bg-red-100 text-red-700',
                                'icon'       => 'timer_off',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                            \App\Enums\CandidateStatus::BLACKLISTED      => [
                                'badge'      => 'Tidak Lolos',
                                'badgeCls'   => 'bg-red-900/10 text-red-800 border border-red-900/20',
                                'icon'       => 'close',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                            default            => [
                                'badge'      => $candidate->status->value,
                                'badgeCls'   => 'bg-surface-container-high text-on-surface-variant',
                                'icon'       => 'help',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                        };
                    @endphp

                    <div class="min-h-[360px] bg-surface-container-lowest rounded-xl p-6 border border-surface-container-high opacity-85 flex flex-col items-center justify-center text-center max-w-sm mx-auto w-full shadow-[0_20px_30px_rgba(0,0,0,0.02)]">
                        {{-- Info Lamaran --}}
                        <div class="mb-4">
                            <h3 class="font-title-md text-title-md text-on-surface font-bold">
                                {{ $candidate->vacancy?->title ?: ($candidate->vacancy?->job_title ?? 'Posisi tidak tersedia') }}
                            </h3>
                            <p class="font-body-md text-xs text-on-surface-variant mt-1">
                                Dikirim pada {{ $candidate->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>

                        {{-- Badge Status & Ikon --}}
                        <div class="flex flex-col items-center gap-3 w-full">
                            {{-- Badge status --}}
                            <span class="font-label-sm text-xs px-4 py-1.5 rounded-full inline-flex items-center gap-1.5 {{ $config['badgeCls'] }} font-semibold">
                                <span class="material-symbols-outlined text-[14px]">{{ $config['icon'] }}</span>
                                {{ $config['badge'] }}
                            </span>

                            {{-- Lingkaran statis (tanpa ping) --}}
                            <div class="flex flex-col items-center">
                                <div class="w-11 h-11 rounded-full {{ $config['circleCls'] }} flex items-center justify-center shadow-sm mb-2">
                                    <span class="material-symbols-outlined text-[20px]">{{ $config['icon'] }}</span>
                                </div>
                                <span class="font-label-sm text-[10px] {{ $config['labelCls'] }} font-bold uppercase tracking-wider">
                                    {{ $config['label'] }}
                                </span>
                            </div>
                        </div>

                        {{-- Rejection Actions --}}
                        @if($candidate->status === \App\Enums\CandidateStatus::REJECTED)
                            <div class="w-full mt-4">
                                <button type="button" wire:click="showRejection({{ $candidate->id }})" 
                                        class="w-full py-2.5 bg-red-50 hover:bg-red-100/80 text-red-700 font-bold text-xs rounded-lg border border-red-200 transition-colors shadow-sm flex items-center justify-center gap-2">
                                    <span class="material-symbols-outlined text-[18px]">mail</span>
                                    <span>Lihat Surat Penolakan</span>
                                </button>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- ===== MODAL SURAT PENOLAKAN ===== --}}
    @if($showRejectModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto" x-cloak>
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm transition-opacity" wire:click="closeRejectModal"></div>
            
            <!-- Modal Dialog Box -->
            <div class="relative bg-surface-container-lowest rounded-xl w-full max-w-lg mx-4 p-6 shadow-2xl border border-surface-container-high z-10 transition-all">
                <!-- Modal Header -->
                <div class="flex justify-between items-center pb-4 mb-4 border-b border-surface-container-high/60">
                    <div class="flex items-center gap-2 text-red-700">
                        <span class="material-symbols-outlined text-[22px]">mail</span>
                        <h3 class="text-base font-bold text-on-surface">Surat Penolakan Lamaran</h3>
                    </div>
                    <button type="button" wire:click="closeRejectModal" class="p-1 hover:bg-surface-container rounded-lg text-on-surface-variant transition-colors" title="Tutup">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                
                <!-- Modal Content -->
                <div class="space-y-4 text-xs text-on-surface-variant leading-relaxed bg-surface-container-low/20 p-5 rounded-lg border border-surface-container">
                    @include('emails.templates.rejected-text', [
                        'name' => $selectedRejectCandidateName,
                        'jobTitle' => $selectedRejectJobTitle
                    ])
                </div>

                <!-- Modal Footer -->
                <div class="flex justify-end pt-4 mt-2">
                    <button type="button" wire:click="closeRejectModal" 
                            class="px-4 py-2 bg-primary text-white font-bold rounded-lg hover:bg-primary-container transition-all active:scale-95 text-xs shadow-md">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
