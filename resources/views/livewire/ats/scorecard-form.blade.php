<div class="max-w-3xl mx-auto bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30">
    <!-- Header -->
    <div class="mb-6 pb-4 border-b border-surface-container-high/50 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h3 class="text-headline-lg text-on-surface mb-1">Isi Scorecard Evaluasi</h3>
            <p class="text-body-md text-sm text-on-surface-variant/70">
                Kandidat: <span class="font-bold text-primary">{{ $candidate->nama }}</span> | Stage: <span class="font-bold text-primary">{{ $stage->nama }}</span>
            </p>
        </div>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="save" class="space-y-6">
        
        <!-- General Validation Errors -->
        @error('kriteriaList')
            <p class="text-xs text-error font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">error</span>
                {{ $message }}
            </p>
        @enderror

        <!-- Predefined Rows List -->
        <div class="space-y-4">
            @foreach($kriteriaList as $index => $item)
                <div class="p-4 rounded-md border border-surface-container-high/60 bg-surface-container-low/20 flex flex-col sm:flex-row gap-6 items-stretch sm:items-center relative group">
                    
                    <!-- Criterion Name (Read-only) -->
                    <div class="flex-1">
                        <label class="block font-bold text-[10px] uppercase tracking-wider text-on-surface-variant mb-1">Kriteria Penilaian</label>
                        <span class="text-sm font-semibold text-on-surface block">{{ $item['kriteria'] }}</span>
                    </div>

                    <!-- Weight percentage (Read-only) -->
                    <div class="w-full sm:w-28">
                        <label class="block font-bold text-[10px] uppercase tracking-wider text-on-surface-variant mb-1">Bobot</label>
                        <span class="text-sm font-bold text-primary block">{{ $item['bobot'] }}%</span>
                    </div>

                    <!-- Score 1-10 (Editable) -->
                    <div class="w-full sm:w-32">
                        <label class="block font-bold text-[10px] uppercase tracking-wider text-on-surface-variant mb-1.5">Nilai (1-10) <span class="text-error">*</span></label>
                        <input type="number" min="1" max="10" wire:model.blur="kriteriaList.{{ $index }}.nilai" 
                               placeholder="1 - 10"
                               class="w-full px-3 h-10 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-xs font-semibold text-on-surface @error('kriteriaList.'.$index.'.nilai') border-error @enderror">
                        @error('kriteriaList.'.$index.'.nilai')
                            <p class="mt-1 text-[10px] text-error font-semibold flex items-center gap-0.5">
                                <span class="material-symbols-outlined text-[12px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Real-time calculation summaries panel -->
        <div class="p-5 rounded-md border border-surface-container-high bg-surface-container-low/40 flex flex-col sm:flex-row justify-between gap-4">
            
            <!-- Weights Checker Indicator -->
            <div class="flex items-center gap-3">
                @php
                    $isCorrectWeight = $totalBobot === 100;
                @endphp
                <div class="w-10 h-10 rounded-full flex items-center justify-center
                    {{ $isCorrectWeight ? 'bg-green-500/10 text-green-600' : 'bg-orange-500/10 text-orange-600' }}">
                    <span class="material-symbols-outlined text-[24px]">
                        {{ $isCorrectWeight ? 'check_circle' : 'hourglass_empty' }}
                    </span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/65">Status Total Bobot</span>
                    <span class="text-sm font-bold {{ $isCorrectWeight ? 'text-green-600' : 'text-orange-600' }}">
                        {{ $totalBobot }}% / 100%
                    </span>
                </div>
            </div>

            <!-- Weighted Score Result Indicator -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-[24px]">calculate</span>
                </div>
                <div>
                    <span class="block text-[10px] font-bold uppercase tracking-wider text-on-surface-variant/65">Nilai Rata-rata Berbobot</span>
                    <span class="text-sm font-bold text-primary font-headline-lg text-base">
                        {{ $totalWeightedScore }}
                    </span>
                </div>
            </div>

        </div>

        @error('totalBobot')
            <p class="text-xs text-error font-semibold flex items-center gap-1">
                <span class="material-symbols-outlined text-[14px]">error</span>
                {{ $message }}
            </p>
        @enderror

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t border-surface-container-high/50">
            <a href="{{ route('ats.candidate.detail', ['candidateId' => $candidateId]) }}" 
               class="inline-flex items-center justify-center px-5 h-12 border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                Batal
            </a>
            <button type="submit" 
                    class="px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.18)] text-sm">
                Simpan Scorecard
            </button>
        </div>
    </form>
</div>
