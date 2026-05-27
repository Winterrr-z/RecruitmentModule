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
                        $stageName = $candidate->currentStage?->nama;
                        $stageIcon = $this->getStageIcon($stageName);
                    @endphp

                    <div class="aspect-square bg-surface-container-lowest rounded-xl p-8 shadow-[0_30px_40px_rgba(107,56,212,0.04)] hover:shadow-[0_40px_50px_rgba(107,56,212,0.06)] transition-shadow duration-300 flex flex-col items-center justify-center text-center max-w-sm mx-auto w-full">
                        {{-- Info Lamaran --}}
                        <div class="mb-6">
                            <h3 class="font-title-md text-title-md text-on-surface">
                                {{ $candidate->lowongan?->jabatan ?? 'Posisi tidak tersedia' }}
                            </h3>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                                Dikirim pada {{ $candidate->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>

                        {{-- Stage & Status --}}
                        <div class="flex flex-col items-center gap-4">
                            {{-- Badge tahap --}}
                            <span class="bg-tertiary-fixed text-on-tertiary-fixed font-label-sm text-label-sm px-4 py-2 rounded-full inline-flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">{{ $stageIcon }}</span>
                                {{ $stageName ?? 'Menunggu' }}
                            </span>

                            {{-- Lingkaran berdenyut --}}
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full bg-primary-container text-on-primary-container flex items-center justify-center shadow-lg ring-4 ring-primary-container/20 mb-2 relative">
                                    <span class="material-symbols-outlined text-[24px]">{{ $stageIcon }}</span>
                                    <div class="absolute inset-0 rounded-full animate-ping bg-primary-container/30"></div>
                                </div>
                                <span class="font-label-sm text-label-sm text-primary font-bold uppercase tracking-wider">
                                    Status Aktif
                                </span>
                            </div>
                        </div>
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
                            'Hired'            => [
                                'badge'      => 'Diterima',
                                'badgeCls'   => 'bg-green-100 text-green-700',
                                'icon'       => 'check_circle',
                                'circleCls'  => 'bg-green-100 text-green-600',
                                'label'      => 'Selesai',
                                'labelCls'   => 'text-green-600',
                            ],
                            'Ditolak'          => [
                                'badge'      => 'Tidak Lolos',
                                'badgeCls'   => 'bg-surface-container-high text-on-surface-variant',
                                'icon'       => 'close',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                            'Offering Expired' => [
                                'badge'      => 'Kedaluwarsa',
                                'badgeCls'   => 'bg-surface-container-high text-on-surface-variant',
                                'icon'       => 'timer_off',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                            default            => [
                                'badge'      => $candidate->status,
                                'badgeCls'   => 'bg-surface-container-high text-on-surface-variant',
                                'icon'       => 'help',
                                'circleCls'  => 'bg-surface-container-high text-on-surface-variant',
                                'label'      => 'Arsip',
                                'labelCls'   => 'text-on-surface-variant',
                            ],
                        };
                    @endphp

                    <div class="aspect-square bg-surface-container-lowest rounded-xl p-8 border border-surface-container-high opacity-60 grayscale flex flex-col items-center justify-center text-center max-w-sm mx-auto w-full">
                        {{-- Info Lamaran --}}
                        <div class="mb-6">
                            <h3 class="font-title-md text-title-md text-on-surface">
                                {{ $candidate->lowongan?->jabatan ?? 'Posisi tidak tersedia' }}
                            </h3>
                            <p class="font-body-md text-body-md text-on-surface-variant mt-1">
                                Dikirim pada {{ $candidate->created_at->translatedFormat('d F Y') }}
                            </p>
                        </div>

                        {{-- Badge Status & Ikon --}}
                        <div class="flex flex-col items-center gap-4">
                            {{-- Badge status --}}
                            <span class="font-label-sm text-label-sm px-4 py-2 rounded-full inline-flex items-center gap-1.5 {{ $config['badgeCls'] }}">
                                <span class="material-symbols-outlined text-[14px]">{{ $config['icon'] }}</span>
                                {{ $config['badge'] }}
                            </span>

                            {{-- Lingkaran statis (tanpa ping) --}}
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-full {{ $config['circleCls'] }} flex items-center justify-center shadow-sm mb-2">
                                    <span class="material-symbols-outlined text-[24px]">{{ $config['icon'] }}</span>
                                </div>
                                <span class="font-label-sm text-label-sm {{ $config['labelCls'] }} font-bold uppercase tracking-wider">
                                    {{ $config['label'] }}
                                </span>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
    @endif
</div>
