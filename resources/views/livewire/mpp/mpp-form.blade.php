<div>
    <x-breadcrumb :items="[['label' => 'Manpower Planning', 'url' => route('mpp.index')], ['label' => isset($mppId) ? 'Edit' : 'Tambah', 'url' => null]]" />
    <!-- Content Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">
                {{ $isEdit ? 'Edit Manpower Planning' : 'Tambah Manpower Planning' }}
            </h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">
                Silakan lengkapi form di bawah ini untuk {{ $isEdit ? 'memperbarui' : 'membuat' }} manpower plan.
            </p>
        </div>
        <a href="{{ route('mpp.index') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-surface-container-low text-on-surface font-bold rounded-md hover:bg-surface-container transition-all active:scale-95 border border-surface-container/50">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Container -->
    <div class="bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30">
        <form wire:submit.prevent="save" class="space-y-6">
            <!-- Row 1: Nama Plan (full width) -->
            <div>
                <label for="nama_plan" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Nama Plan <span class="text-error">*</span></label>
                <input type="text" id="nama_plan" wire:model="nama_plan" 
                       class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('nama_plan') ring-2 ring-error/20 @enderror"
                       placeholder="Masukkan Nama Plan (cth: Rekrutmen Designer Q3)">
                @error('nama_plan')
                    <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                @enderror
            </div>

            <!-- Row 2: Departemen (kiri) & Jabatan (kanan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="departemen" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Departemen <span class="text-error">*</span></label>
                    <input type="text" id="departemen" wire:model="departemen" 
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('departemen') ring-2 ring-error/20 @enderror"
                           placeholder="Masukkan Departemen (cth: Technology)">
                    @error('departemen')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="jabatan" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Jabatan <span class="text-error">*</span></label>
                    <input type="text" id="jabatan" wire:model="jabatan" 
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('jabatan') ring-2 ring-error/20 @enderror"
                           placeholder="Masukkan Jabatan (cth: UI/UX Designer)">
                    @error('jabatan')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 3: Jumlah Kebutuhan (kiri) & SLA dalam bulan (kanan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="jumlah_kebutuhan" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Jumlah Kebutuhan (Orang) <span class="text-error">*</span></label>
                    <input type="number" id="jumlah_kebutuhan" wire:model="jumlah_kebutuhan" min="1"
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('jumlah_kebutuhan') ring-2 ring-error/20 @enderror"
                           placeholder="Jumlah Orang">
                    @error('jumlah_kebutuhan')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="sla_hari" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">SLA (Hari) <span class="text-error">*</span></label>
                    <input type="number" id="sla_hari" wire:model.live="sla_hari" min="1"
                           class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('sla_hari') ring-2 ring-error/20 @enderror"
                           placeholder="Estimasi Durasi (hari)">
                    
                    <!-- Real-time target date display -->
                    @if ($target_waktu_absolut)
                        <p class="text-xs text-on-surface-variant/80 mt-2 flex items-center gap-1 px-3">
                            <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span>
                            <span>Target selesai rekrutmen: <strong>{{ \Carbon\Carbon::parse($target_waktu_absolut)->translatedFormat('d F Y') }}</strong></span>
                        </p>
                    @endif

                    @error('sla_hari')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 4: Estimasi Gaji Min (kiri) & Estimasi Gaji Max (kanan) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="estimasi_gaji_min" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Estimasi Gaji Min</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-on-surface-variant/70 font-semibold">Rp</span>
                        <input type="text" id="estimasi_gaji_min" wire:model.blur="estimasi_gaji_min"
                               class="w-full pl-14 pr-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('estimasi_gaji_min') ring-2 ring-error/20 @enderror"
                               placeholder="Contoh: 10,000,000">
                    </div>
                    @error('estimasi_gaji_min')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="estimasi_gaji_max" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Estimasi Gaji Max</label>
                    <div class="relative">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-on-surface-variant/70 font-semibold">Rp</span>
                        <input type="text" id="estimasi_gaji_max" wire:model.blur="estimasi_gaji_max"
                               class="w-full pl-14 pr-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('estimasi_gaji_max') ring-2 ring-error/20 @enderror"
                               placeholder="Contoh: 15,000,000">
                    </div>
                    @error('estimasi_gaji_max')
                        <p class="text-error text-xs mt-1 px-3 font-semibold">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Row 5: Note (full width) -->
            <div>
                <label for="note" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">Note</label>
                <textarea id="note" wire:model="note" rows="3"
                          class="w-full px-6 py-4 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('note') ring-2 ring-error/20 @enderror"
                          placeholder="Tambahkan catatan khusus untuk perencanaan kebutuhan ini..."></textarea>
                @error('note')
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