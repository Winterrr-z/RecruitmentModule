<div>
    <!-- Flash Message Notification -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-green-500/10 text-green-700 border border-green-500/20 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="font-body-md">{{ session('message') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- Content Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Manpower Planning</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Kelola perencanaan kebutuhan tenaga kerja perusahaan Anda.</p>
        </div>
        <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Tambah Planning</span>
        </button>
    </div>

    <!-- Cards Grid -->
    @if($mpps->isEmpty())
        <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-lg border border-dashed border-outline-variant/50 shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.04)]">
            <span class="material-symbols-outlined text-[64px] text-on-surface-variant/30 mb-4">group_add</span>
            <h3 class="text-title-md font-title-md text-on-surface mb-2">Belum Ada Manpower Planning</h3>
            <p class="text-label-sm font-label-sm text-on-surface-variant max-w-md mb-6">
                Mulai rencanakan kebutuhan tenaga kerja baru untuk departemen Anda dengan membuat manpower plan pertama.
            </p>
            <button wire:click="openCreateModal" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
                <span class="material-symbols-outlined text-[20px]">add</span>
                <span>Buat Plan Pertama</span>
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($mpps as $mpp)
                <div class="group relative bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] hover:shadow-[0px_40px_80px_-15px_rgba(107,56,212,0.1)] transition-all duration-300">
                    <div class="absolute top-6 right-6 flex items-center gap-2 px-3 py-1 bg-error/10 text-error rounded-md text-[11px] font-bold">
                        <span class="w-2 h-2 bg-error rounded-full animate-pulse"></span>
                        Critical
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-3">
                            @if(strtolower($mpp->status) === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold rounded uppercase">Approved</span>
                            @else
                                <span class="px-2 py-1 bg-surface-container-highest text-on-surface-variant text-[10px] font-bold rounded uppercase">Draft</span>
                            @endif
                        </div>
                        <h4 class="text-title-md font-title-md text-on-surface group-hover:text-primary transition-colors">
                            <a href="{{ route('mpp.show', ['mppId' => $mpp->id]) }}">
                                {{ $mpp->nama_plan }}
                            </a>
                        </h4>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">
                            {{ $mpp->departemen }}
                        </p>
                    </div>
                    <div class="space-y-4 pt-4 border-t border-surface-container">
                        <div class="flex justify-between items-center">
                            <span class="text-label-sm font-label-sm text-on-surface-variant">Kuota:</span>
                            <span class="text-label-sm font-label-sm font-bold">
                                {{ $mpp->jumlah_kebutuhan }} Orang
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-label-sm font-label-sm text-on-surface-variant">Estimasi Gaji:</span>
                            <span class="text-label-sm font-label-sm font-bold text-primary">
                                @if($mpp->estimasi_gaji_min && $mpp->estimasi_gaji_max)
                                    Rp {{ number_format($mpp->estimasi_gaji_min, 0, ',', '.') }} - Rp {{ number_format($mpp->estimasi_gaji_max, 0, ',', '.') }}
                                @elseif($mpp->estimasi_gaji_min)
                                    Rp {{ number_format($mpp->estimasi_gaji_min, 0, ',', '.') }}
                                @elseif($mpp->estimasi_gaji_max)
                                    Rp {{ number_format($mpp->estimasi_gaji_max, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>
                    <!-- Action Menu -->
                    <div class="absolute bottom-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2">
                        <button wire:click="openEditModal({{ $mpp->id }})" class="p-2 hover:bg-surface-container-low rounded-md transition-colors text-on-surface-variant">
                            <span class="material-symbols-outlined text-[20px]">edit</span>
                        </button>
                        <button wire:click="delete({{ $mpp->id }})" wire:confirm="Apakah Anda yakin ingin menghapus manpower plan ini?" class="p-2 hover:bg-error/10 rounded-md transition-colors text-error">
                            <span class="material-symbols-outlined text-[20px]">delete</span>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Form Modal -->
    @if ($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 sm:p-6">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-on-surface/40 backdrop-blur-sm transition-opacity" wire:click="closeModal"></div>
                
                <!-- Modal Box -->
                <div class="relative bg-surface-container-lowest p-8 rounded-md shadow-2xl max-w-2xl w-full z-10 border border-surface-container/50 transform transition-all duration-300 scale-100">
                <!-- Close Button -->
                <button wire:click="closeModal" class="absolute top-6 right-6 p-2 text-on-surface-variant hover:text-primary hover:bg-surface-container-low rounded-md transition-all">
                    <span class="material-symbols-outlined text-[24px]">close</span>
                </button>

                <!-- Title -->
                <h3 class="font-headline-lg text-headline-lg text-on-surface mb-6">
                    {{ $isEdit ? 'Edit Manpower Planning' : 'Tambah Manpower Planning' }}
                </h3>

                <!-- Form -->
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
                            <label for="sla_bulan" class="block text-label-sm font-label-sm text-on-surface-variant mb-2">SLA (Bulan) <span class="text-error">*</span></label>
                            <input type="number" id="sla_bulan" wire:model.live="sla_bulan" min="1"
                                   class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('sla_bulan') ring-2 ring-error/20 @enderror"
                                   placeholder="Estimasi Durasi (bulan)">
                            
                            <!-- Real-time target date display -->
                            @if ($target_waktu_absolut)
                                <p class="text-xs text-on-surface-variant/80 mt-2 flex items-center gap-1 px-3">
                                    <span class="material-symbols-outlined text-[16px] text-primary">calendar_month</span>
                                    <span>Target selesai rekrutmen: <strong>{{ \Carbon\Carbon::parse($target_waktu_absolut)->translatedFormat('d F Y') }}</strong></span>
                                </p>
                            @endif

                            @error('sla_bulan')
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
                        <button type="button" wire:click="closeModal" 
                                class="inline-flex items-center justify-center px-6 h-12 text-on-surface-variant hover:bg-surface-container-low font-bold rounded-md transition-colors active:scale-95">
                            Batal
                        </button>
                        <button type="submit" 
                                class="inline-flex items-center justify-center px-8 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
