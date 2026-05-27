<div class="max-w-xl mx-auto bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30">
    <!-- Header -->
    <div class="mb-6 pb-4 border-b border-surface-container-high/50">
        <h3 class="text-headline-lg text-on-surface mb-1">Atur Jadwal Interview</h3>
        <p class="text-body-md text-sm text-on-surface-variant/70">
            Kandidat: <span class="font-bold text-primary">{{ $candidate->nama }}</span> | Stage: <span class="font-bold text-primary">{{ $stage->nama }}</span>
        </p>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="save" class="space-y-6">
        
        <!-- Tanggal & Waktu Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Tanggal -->
            <div>
                <label for="tanggal" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Tanggal Wawancara <span class="text-error">*</span></label>
                <input type="date" id="tanggal" wire:model="tanggal" 
                       class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('tanggal') border-error @enderror">
                @error('tanggal')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Waktu -->
            <div>
                <label for="waktu" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Waktu Wawancara <span class="text-error">*</span></label>
                <input type="time" id="waktu" wire:model="waktu" 
                       class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('waktu') border-error @enderror">
                @error('waktu')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <!-- Tempat -->
        <div>
            <label for="tempat" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Lokasi / Ruangan (On-site)</label>
            <input type="text" id="tempat" wire:model="tempat" 
                   placeholder="Contoh: Ruang Meeting Lantai 3, Gedung Utama"
                   class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('tempat') border-error @enderror">
            @error('tempat')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Tautan Virtual -->
        <div>
            <label for="tautan_virtual" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Tautan Virtual Meeting (Remote)</label>
            <input type="url" id="tautan_virtual" wire:model="tautan_virtual" 
                   placeholder="Contoh: https://meet.google.com/abc-defg-hij"
                   class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('tautan_virtual') border-error @enderror">
            @error('tautan_virtual')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t border-surface-container-high/50">
            <a href="{{ route('ats.candidate.detail', ['candidateId' => $candidateId]) }}" 
               class="inline-flex items-center justify-center px-5 h-12 border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                Batal
            </a>
            <button type="submit" 
                    class="px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.18)] text-sm">
                Simpan Jadwal
            </button>
        </div>
    </form>
</div>
