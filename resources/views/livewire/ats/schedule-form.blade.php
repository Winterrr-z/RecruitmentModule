<div>
    <x-breadcrumb :items="[['label' => 'ATS', 'url' => null], ['label' => 'All Candidates', 'url' => route('ats.dashboard')], ['label' => $candidate->name ?? 'Detail Kandidat', 'url' => route('ats.candidate.detail', ['candidateId' => $candidate->id])], ['label' => 'Atur Jadwal', 'url' => null]]" />
    
    <div class="max-w-xl mx-auto bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30">
        <!-- Header -->
    <div class="mb-6 pb-4 border-b border-surface-container-high/50">
        <h3 class="text-headline-lg text-on-surface mb-1">Atur Jadwal Interview</h3>
        <p class="text-body-md text-sm text-on-surface-variant/70">
            Kandidat: <span class="font-bold text-primary">{{ $candidate->name }}</span> | Stage: <span class="font-bold text-primary">{{ $stage->name }}</span>
        </p>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="save" class="space-y-6">
        
        <!-- Tanggal & Waktu Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Tanggal -->
            <div>
                <label for="date" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Tanggal Wawancara <span class="text-error">*</span></label>
                <input type="date" id="date" wire:model="date" 
                       class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('date') border-error @enderror">
                @error('date')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Waktu -->
            <div>
                <label for="time" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Waktu Wawancara <span class="text-error">*</span></label>
                <input type="time" id="time" wire:model="time" 
                       class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('time') border-error @enderror">
                @error('time')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <!-- Tempat -->
        <div>
            <label for="venue" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Lokasi / Ruangan (On-site)</label>
            <input type="text" id="venue" wire:model="venue" 
                   placeholder="Contoh: Ruang Meeting Lantai 3, Gedung Utama"
                   class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('venue') border-error @enderror">
            @error('venue')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Tautan Virtual -->
        <div>
            <label for="virtual_link" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Tautan Virtual Meeting (Remote)</label>
            <input type="url" id="virtual_link" wire:model="virtual_link" 
                   placeholder="Contoh: https://meet.google.com/abc-defg-hij"
                   class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('virtual_link') border-error @enderror">
            @error('virtual_link')
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
</div>