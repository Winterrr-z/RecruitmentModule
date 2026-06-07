<div>
    <x-breadcrumb :items="[['label' => 'ATS', 'url' => null], ['label' => $backLabel, 'url' => $backUrl], ['label' => $candidate->name, 'url' => route('ats.candidate.detail', ['candidateId' => $candidate->id]) . '?from=' . ($backLabel === 'All Candidates' ? 'candidates' : 'dashboard')], ['label' => 'Offering Letter', 'url' => null]]" />

    <!-- Content Header & Back button -->
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('ats.candidate.detail', ['candidateId' => $candidateId]) }}?from={{ $backLabel === 'All Candidates' ? 'candidates' : 'dashboard' }}" class="p-2 hover:bg-surface-container rounded-md transition-colors text-on-surface-variant flex items-center" title="Kembali ke Detail Pelamar">
            <span class="material-symbols-outlined">arrow_back</span>
        </a>
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Kirim Surat Penawaran (Offering Letter)</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Kirim penawaran pekerjaan resmi ke email kandidat yang terpilih</p>
        </div>
    </div>

    <x-toast-alert />

    <div class="max-w-4xl mx-auto bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 flex flex-col gap-6">
        <div>
            <h3 class="text-title-md font-headline-lg text-on-surface mb-2">Detail Kandidat & Lowongan</h3>
            <p class="text-body-md text-xs text-on-surface-variant/70">Periksa kembali data di bawah ini sebelum mengirimkan surat penawaran.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 p-6 bg-surface-container-low/40 rounded-md border border-surface-container/40">
            <!-- Candidate Info -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-surface-container-high/65 pb-1">Informasi Pelamar</h4>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Nama Lengkap</span>
                    <span class="text-body-md text-on-surface font-semibold">{{ $candidate->name }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Email</span>
                    <span class="text-body-md text-on-surface font-semibold">{{ $candidate->email }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Tahap Saat Ini</span>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-primary/10 text-primary border border-primary/20">
                        {{ $candidate->currentStage->name }}
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Status</span>
                    <span class="inline-flex items-center gap-1 mt-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/10 text-blue-700 border border-blue-500/20">
                        {{ $candidate->status->value ?? $candidate->status }}
                    </span>
                </div>
            </div>

            <!-- Job Info -->
            <div class="space-y-4">
                <h4 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-surface-container-high/65 pb-1">Informasi Lowongan</h4>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Jabatan</span>
                    <span class="text-body-md text-on-surface font-semibold">{{ $lowongan->job_title }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Departemen</span>
                    <span class="text-body-md text-on-surface font-semibold">{{ $lowongan->department }}</span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Kuota Tersisa</span>
                    <span class="text-body-md font-bold {{ $lowongan->quota > 0 ? 'text-green-600' : 'text-error' }}">
                        {{ $lowongan->quota }} posisi
                    </span>
                </div>
                <div>
                    <span class="block text-[11px] font-bold uppercase tracking-wider text-on-surface-variant/60">Tipe / Lokasi</span>
                    <span class="text-body-md text-on-surface font-semibold capitalize">{{ $lowongan->employment_type }} ({{ $lowongan->location }})</span>
                </div>
            </div>
        </div>

        <!-- Preview Surat Penawaran -->
        <div>
            <h4 class="text-xs font-bold uppercase tracking-wider text-primary border-b border-surface-container-high/65 pb-1 mb-3">Preview Surat Penawaran</h4>
            <div class="text-xs text-on-surface-variant/90 leading-relaxed space-y-3 max-h-48 overflow-y-auto p-4 border rounded-md bg-surface/30">
                @include('emails.templates.offering-text', ['name' => $candidate->name, 'jobTitle' => $lowongan->job_title])
            </div>
        </div>

        @if (!$isValid)
            <!-- Validation Error Alert -->
            <div class="p-4 rounded-md bg-error/10 text-error border border-error/25 flex items-start gap-3">
                <span class="material-symbols-outlined mt-0.5">error</span>
                <div>
                    <h4 class="font-bold text-sm">Persyaratan Tidak Terpenuhi</h4>
                    <p class="text-xs mt-1 leading-relaxed">{{ $errorMessage }}</p>
                </div>
            </div>
        @else
            <!-- Success Info Box / Guidelines -->
            <div class="p-4 rounded-md bg-green-500/10 text-green-800 border border-green-500/25 flex items-start gap-3">
                <span class="material-symbols-outlined mt-0.5">info</span>
                <div class="text-xs leading-relaxed">
                    <p class="font-bold text-sm text-green-900 mb-1">Informasi Pengiriman:</p>
                    <ul class="list-disc pl-4 space-y-1">
                        <li>Kandidat akan menerima email berisi tautan tinjauan unik.</li>
                        <li>Kandidat memiliki waktu <strong>3 hari</strong> untuk memberikan keputusan (Terima/Tolak).</li>
                        <li>Apabila melewati tenggat, status penawaran akan otomatis kedaluwarsa.</li>
                    </ul>
                </div>
            </div>
        @endif

        <!-- Action Row -->
        <div class="flex justify-end gap-3 pt-6 border-t border-surface-container-high/50">
            <a href="{{ route('ats.candidate.detail', ['candidateId' => $candidateId]) }}" 
               class="px-5 h-12 border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm flex items-center justify-center">
                Batal
            </a>
            @if ($isValid)
                <button wire:click="sendOffering" wire:loading.attr="disabled"
                        class="px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.18)] text-sm flex items-center gap-2 justify-center">
                    <span wire:loading.remove wire:target="sendOffering" class="material-symbols-outlined text-[20px]">mail</span>
                    <span wire:loading wire:target="sendOffering" class="w-5 h-5 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <span>Kirim Offering Letter</span>
                </button>
            @else
                <button disabled 
                        class="px-6 h-12 bg-surface-container text-on-surface-variant/40 font-bold rounded-md cursor-not-allowed text-sm">
                    Kirim Offering Letter
                </button>
            @endif
        </div>
    </div>
</div>
