<div>
    <x-breadcrumb :items="[['label' => 'ATS', 'url' => null], ['label' => 'Pipeline', 'url' => route('ats.dashboard')], ['label' => 'Tambah Manual', 'url' => null]]" />
    <div class="max-w-2xl mx-auto bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30">
    <!-- Header -->
    <div class="mb-6 pb-4 border-b border-surface-container-high/50">
        <h3 class="text-headline-lg text-on-surface mb-1">Tambah Kandidat secara Manual</h3>
        <p class="text-body-md text-sm text-on-surface-variant/70">
            @if($vacancy)
                Form pendaftaran pelamar secara manual untuk Lowongan Kerja: <span class="font-bold text-primary">{{ $vacancy->job_title }} ({{ $vacancy->department }})</span>
            @else
                Form pendaftaran pelamar secara manual sebagai <span class="font-bold text-primary">Kandidat Mandiri (Tanpa Lowongan Kerja)</span>
            @endif
        </p>
    </div>

    <!-- Form -->
    <form wire:submit.prevent="save" class="space-y-6">

        <!-- Lowongan Kerja -->
        <div>
            <label for="vacancyId" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Lowongan Kerja (Opsional)</label>
            <select id="vacancyId" wire:model.live="vacancyId" 
                    class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface cursor-pointer @error('vacancyId') border-error @enderror">
                <option value="">Kandidat Mandiri (Tanpa Lowongan)</option>
                @foreach($vacancies as $vac)
                    <option value="{{ $vac->id }}">{{ $vac->job_title }} ({{ $vac->department }})</option>
                @endforeach
            </select>
            @error('vacancyId')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Nama Lengkap -->
        <div>
            <label for="name" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nama Lengkap <span class="text-error">*</span></label>
            <input type="text" id="name" wire:model="name" 
                   placeholder="Contoh: Budi Santoso"
                   class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('name') border-error @enderror">
            @error('name')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Email & Telepon Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
            <!-- Email -->
            <div>
                <label for="email" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Alamat Email <span class="text-error">*</span></label>
                <input type="email" id="email" wire:model="email" 
                       placeholder="Contoh: budi@gmail.com"
                       class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('email') border-error @enderror">
                @error('email')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Telepon -->
            <div>
                <label for="phone" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nomor Telepon <span class="text-error">*</span></label>
                <input type="text" id="phone" wire:model="phone" 
                       placeholder="Contoh: 08123456789"
                       class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('phone') border-error @enderror">
                @error('phone')
                    <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        {{ $message }}
                    </p>
                @enderror
            </div>
        </div>

        <!-- CV Upload -->
        <div>
            <label for="cv" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">File Curriculum Vitae (CV) <span class="text-error">*</span></label>
            <input type="file" id="cv" wire:model="cv" accept=".pdf"
                   class="w-full text-xs text-on-surface-variant/80 border border-surface-container rounded-md file:mr-4 file:py-3 file:px-4 file:rounded-l-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer cursor-pointer">
            
            <div class="mt-1 flex items-center gap-2">
                <span class="text-[10px] text-on-surface-variant/50">Maksimal 5MB, format PDF saja.</span>
                <span wire:loading wire:target="cv" class="text-[10px] text-primary font-bold flex items-center gap-0.5 animate-pulse">
                    <span class="material-symbols-outlined text-[12px] animate-spin">sync</span>
                    Mengunggah...
                </span>
            </div>

            @error('cv')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Portofolio Upload -->
        <div>
            <label for="portofolio" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">File Portofolio (Opsional)</label>
            <input type="file" id="portofolio" wire:model="portofolio" accept=".pdf"
                   class="w-full text-xs text-on-surface-variant/80 border border-surface-container rounded-md file:mr-4 file:py-3 file:px-4 file:rounded-l-md file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 file:cursor-pointer cursor-pointer">
            
            <div class="mt-1 flex items-center gap-2">
                <span class="text-[10px] text-on-surface-variant/50">Maksimal 5MB, format PDF saja.</span>
                <span wire:loading wire:target="portofolio" class="text-[10px] text-primary font-bold flex items-center gap-0.5 animate-pulse">
                    <span class="material-symbols-outlined text-[12px] animate-spin">sync</span>
                    Mengunggah...
                </span>
            </div>

            @error('portofolio')
                <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-6 border-t border-surface-container-high/50">
            <a href="{{ route('ats.dashboard') }}" 
               class="inline-flex items-center justify-center px-5 h-12 border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                Batal
            </a>
            <button type="submit" 
                    wire:loading.attr="disabled" 
                    wire:target="cv, portofolio"
                    class="px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed shadow-[0_4px_12px_rgba(107,56,212,0.18)] text-sm">
                <span wire:loading.remove wire:target="cv, portofolio" class="flex items-center gap-1">
                    Simpan Pelamar
                </span>
                <span wire:loading wire:target="cv, portofolio" class="inline-flex items-center justify-center gap-2">
                    <svg class="animate-spin h-4 w-4 text-white shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
            </button>
        </div>
    </form>
</div>
</div>