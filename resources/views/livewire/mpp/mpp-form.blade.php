<div>
    <x-breadcrumb :items="[['label' => 'Manpower Planning', 'url' => route('mpp.index')], ['label' => isset($mppId) ? 'Edit' : 'Tambah', 'url' => null]]" />
    <!-- Content Header -->
    <div class="mb-8 flex items-center gap-4">
        <a href="{{ route('mpp.index') }}" class="w-10 h-10 flex items-center justify-center rounded-full bg-surface-container-low hover:bg-surface-container transition-all text-on-surface-variant border border-surface-container/50 flex-shrink-0">
            <span class="material-symbols-outlined text-[1.25rem]">arrow_back</span>
        </a>
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">
                {{ $isEdit ? 'Edit Manpower Planning' : 'Tambah Manpower Planning' }}
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">
                Silakan lengkapi form di bawah ini untuk {{ $isEdit ? 'memperbarui' : 'membuat' }} manpower plan.
            </p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30">
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Row 1: Nama Plan (full width) -->
            <div>
                <label for="plan_name" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Nama Plan <span class="text-error">*</span></label>
                <input type="text" id="plan_name" wire:model="form.plan_name" 
                       class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.plan_name') ring-2 ring-error/20 @enderror"
                       placeholder="Masukkan Nama Plan (cth: Rekrutmen Designer Q3)">
                @error('form.plan_name')
                    <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Row 2: Departemen (kiri) & Jabatan (kanan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="department" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Departemen <span class="text-error">*</span></label>
                    <input type="text" id="department" wire:model="form.department" 
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.department') ring-2 ring-error/20 @enderror"
                           placeholder="Masukkan Departemen (cth: Technology)">
                    @error('form.department')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="job_title" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Jabatan <span class="text-error">*</span></label>
                    <input type="text" id="job_title" wire:model="form.job_title" 
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.job_title') ring-2 ring-error/20 @enderror"
                           placeholder="Masukkan Jabatan (cth: UI/UX Designer)">
                    @error('form.job_title')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 3: Jumlah Kebutuhan (kiri) & SLA dalam bulan (kanan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="quota" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Jumlah Kebutuhan (Orang) <span class="text-error">*</span></label>
                    <input type="number" id="quota" wire:model="form.quota" min="1"
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.quota') ring-2 ring-error/20 @enderror"
                           placeholder="Jumlah Orang">
                    @error('form.quota')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sla_days" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">SLA (Hari) <span class="text-error">*</span></label>
                    <input type="number" id="sla_days" wire:model.live="form.sla_days" min="1"
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.sla_days') ring-2 ring-error/20 @enderror"
                           placeholder="Estimasi Durasi (hari)">
                    
                    <!-- Real-time target date display -->
                    @if ($form->absolute_target_date)
                        <p class="text-xs text-on-surface-variant/80 mt-2 flex items-center gap-1 px-3">
                            <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span>
                            <span>Target selesai rekrutmen: <strong>{{ \Carbon\Carbon::parse($form->absolute_target_date)->translatedFormat('d F Y') }}</strong></span>
                        </p>
                    @endif

                    @error('form.sla_days')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 4: Estimasi Gaji Min (kiri) & Estimasi Gaji Max (kanan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="estimated_salary_min" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Estimasi Gaji Min</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-on-surface-variant/70 font-semibold">Rp</span>
                        <input type="text" id="estimated_salary_min" wire:model.blur="form.estimated_salary_min"
                               class="w-full pl-14 pr-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.estimated_salary_min') ring-2 ring-error/20 @enderror"
                               placeholder="Contoh: 10,000,000">
                    </div>
                    @error('form.estimated_salary_min')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="estimated_salary_max" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Estimasi Gaji Max</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-on-surface-variant/70 font-semibold">Rp</span>
                        <input type="text" id="estimated_salary_max" wire:model.blur="form.estimated_salary_max"
                               class="w-full pl-14 pr-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.estimated_salary_max') ring-2 ring-error/20 @enderror"
                               placeholder="Contoh: 15,000,000">
                    </div>
                    @error('form.estimated_salary_max')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 5: Note (full width) -->
            <div>
                <label for="note" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Note</label>
                <textarea id="note" wire:model="form.note" rows="3"
                          class="w-full px-6 py-4 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('form.note') ring-2 ring-error/20 @enderror"
                          placeholder="Tambahkan catatan khusus untuk perencanaan kebutuhan ini..."></textarea>
                @error('form.note')
                    <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit & Cancel Buttons -->
            <div class="flex items-center justify-end gap-4 pt-6 border-t border-surface-container mt-8">
                <a href="{{ route('mpp.index') }}" 
                        class="inline-flex items-center justify-center px-6 h-12 text-on-surface-variant hover:bg-surface-container-low font-bold rounded-md transition-colors active:scale-95">
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center px-8 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>