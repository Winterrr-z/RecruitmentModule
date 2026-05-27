<div>
    <!-- Flash Notifications -->
    @if (session()->has('message'))
        <div class="mb-6 p-4 rounded-lg bg-green-500/10 text-green-700 border border-green-500/20 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-green-600">check_circle</span>
                <span class="font-body-md text-sm font-semibold">{{ session('message') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-green-700 hover:text-green-900 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="mb-6 p-4 rounded-lg bg-error/10 text-error border border-error/20 flex items-center justify-between transition-all duration-300">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-error">warning</span>
                <span class="font-body-md text-sm font-semibold">{{ session('error') }}</span>
            </div>
            <button onclick="this.parentElement.remove()" class="text-error hover:text-error/80 transition-colors">
                <span class="material-symbols-outlined text-[18px]">close</span>
            </button>
        </div>
    @endif

    <!-- Content Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Config Stage ATS</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Atur tahapan proses seleksi rekrutmen kandidat secara dinamis</p>
        </div>
        <button wire:click="openAddModal" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Tambah Stage</span>
        </button>
    </div>

    <!-- Table Card Container -->
    <div class="bg-surface-container-lowest rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-surface-container-high bg-surface-container-low/40">
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Urutan</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Nama Stage</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Deskripsi</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant text-center">Butuh Scorecard</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant text-center">Butuh Jadwal</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container/30">
                    @forelse($stages as $stage)
                        <tr class="hover:bg-surface/30 transition-colors group">
                            <!-- Urutan & Reordering -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-primary bg-primary/5 w-8 h-8 rounded-full flex items-center justify-center text-sm font-headline-lg">
                                        {{ $stage->urutan }}
                                    </span>
                                    <div class="flex flex-col">
                                        <!-- Move Up button -->
                                        @if(!in_array($stage->id, [1, 2]) && $stage->urutan > 2)
                                            <button wire:click="moveUp({{ $stage->id }})" class="text-primary hover:text-primary-container p-0.5 rounded transition-colors" title="Naikkan Urutan">
                                                <span class="material-symbols-outlined text-[18px]">keyboard_arrow_up</span>
                                            </button>
                                        @else
                                            <span class="text-on-surface-variant/20 p-0.5" title="Tidak dapat dinaikkan"><span class="material-symbols-outlined text-[18px]">keyboard_arrow_up</span></span>
                                        @endif

                                        <!-- Move Down button -->
                                        @if(!in_array($stage->id, [1, 2]) && $stage->urutan < ($finalUrutan - 1))
                                            <button wire:click="moveDown({{ $stage->id }})" class="text-primary hover:text-primary-container p-0.5 rounded transition-colors" title="Turunkan Urutan">
                                                <span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span>
                                            </button>
                                        @else
                                            <span class="text-on-surface-variant/20 p-0.5" title="Tidak dapat diturunkan"><span class="material-symbols-outlined text-[18px]">keyboard_arrow_down</span></span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <!-- Nama Stage -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-title-md text-sm font-bold text-on-surface">{{ $stage->nama }}</span>
                                @if(in_array($stage->id, [1, 2]))
                                    <span class="ml-2 px-2 py-0.5 bg-primary/10 text-primary text-[10px] font-bold rounded uppercase tracking-wider">System Default</span>
                                @endif
                            </td>
                            <!-- Deskripsi -->
                            <td class="px-6 py-4">
                                <span class="font-body-md text-sm text-on-surface-variant/80">{{ $stage->deskripsi ?: '-' }}</span>
                            </td>
                            <!-- Butuh Scorecard -->
                            <td class="px-6 py-4 text-center">
                                @if($stage->butuh_scorecard)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-green-500/10 text-green-700 border border-green-500/20 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                        Ya
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-surface-container-high text-on-surface-variant/60 rounded-full text-xs font-medium">
                                        <span class="w-1.5 h-1.5 bg-on-surface-variant/40 rounded-full"></span>
                                        Tidak
                                    </span>
                                @endif
                            </td>
                            <!-- Butuh Jadwal -->
                            <td class="px-6 py-4 text-center">
                                @if($stage->butuh_jadwal)
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary border border-primary/20 rounded-full text-xs font-bold">
                                        <span class="w-1.5 h-1.5 bg-primary rounded-full"></span>
                                        Ya
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-3 py-1 bg-surface-container-high text-on-surface-variant/60 rounded-full text-xs font-medium">
                                        <span class="w-1.5 h-1.5 bg-on-surface-variant/40 rounded-full"></span>
                                        Tidak
                                    </span>
                                @endif
                            </td>
                            <!-- Aksi -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-2">
                                    <!-- Edit Button -->
                                    <button wire:click="editStage({{ $stage->id }})" class="p-2 hover:bg-primary/10 rounded-md transition-colors text-primary" title="Ubah Stage">
                                        <span class="material-symbols-outlined text-[20px]">edit</span>
                                    </button>

                                    <!-- Delete Button -->
                                    @if(in_array($stage->id, [1, 2]))
                                        <!-- Protected system default stage -->
                                        <button class="p-2 text-on-surface-variant/20 cursor-not-allowed" title="Stage sistem tidak dapat dihapus" disabled>
                                            <span class="material-symbols-outlined text-[20px]">delete_forever</span>
                                        </button>
                                    @else
                                        <!-- Custom stage -->
                                        @if($stage->candidates()->count() > 0)
                                            <button class="p-2 text-on-surface-variant/20 cursor-not-allowed" title="Tidak dapat menghapus stage yang memiliki kandidat" disabled>
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        @else
                                            <button wire:click="deleteStage({{ $stage->id }})" wire:confirm="Apakah Anda yakin ingin menghapus stage '{{ $stage->nama }}' ini?" class="p-2 hover:bg-error/10 rounded-md transition-colors text-error" title="Hapus Stage">
                                                <span class="material-symbols-outlined text-[20px]">delete</span>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant/50">
                                Belum ada data stage rekrutmen.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Form (Add / Edit Stage) -->
    <div x-data="{ show: @entangle('showModal') }" 
         x-show="show" 
         class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto"
         style="display: none;">
        <!-- Backdrop -->
        <div x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/45 backdrop-blur-sm"
             @click="show = false"></div>

        <!-- Modal Dialog Box -->
        <div x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95 translate-y-4"
             x-transition:enter-end="opacity-100 scale-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100 translate-y-0"
             x-transition:leave-end="opacity-0 scale-95 translate-y-4"
             class="relative bg-surface-container-lowest rounded-md w-full max-w-lg p-8 mx-4 shadow-[0_24px_48px_-12px_rgba(107,56,212,0.18)] border border-surface-container-high/50 z-10">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-container-high/50">
                <h3 class="text-title-md font-headline-lg text-on-surface">
                    {{ $isEdit ? 'Ubah Stage Rekrutmen' : 'Tambah Stage Rekrutmen Baru' }}
                </h3>
                <button @click="show = false" class="text-on-surface-variant/60 hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Modal Content (Form) -->
            <form wire:submit.prevent="save" class="space-y-6">
                <!-- Nama Stage -->
                <div>
                    <label for="nama" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nama Stage <span class="text-error">*</span></label>
                    <input type="text" id="nama" wire:model="nama" 
                           placeholder="Contoh: Technical Test, HR Interview"
                           class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('nama') border-error @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div>
                    <label for="deskripsi" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Deskripsi Stage</label>
                    <textarea id="deskripsi" wire:model="deskripsi" rows="3"
                              placeholder="Penjelasan singkat mengenai proses seleksi pada tahap ini..."
                              class="w-full p-4 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('deskripsi') border-error @enderror"></textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Toggle Options -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 pt-2">
                    <!-- Butuh Scorecard Toggle -->
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="butuh_scorecard" class="sr-only peer">
                            <div class="w-11 h-6 bg-surface-container-high rounded-full peer peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-outline-variant after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            <span class="ml-3 font-body-md text-sm font-semibold text-on-surface">Butuh Scorecard</span>
                        </label>
                    </div>

                    <!-- Butuh Jadwal Toggle -->
                    <div class="flex items-center">
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" wire:model.live="butuh_jadwal" class="sr-only peer">
                            <div class="w-11 h-6 bg-surface-container-high rounded-full peer peer-focus:ring-2 peer-focus:ring-primary/20 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:left-[2px] after:bg-white after:border-outline-variant after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary"></div>
                            <span class="ml-3 font-body-md text-sm font-semibold text-on-surface">Butuh Jadwal</span>
                        </label>
                    </div>
                </div>

                <!-- Scorecard Template Section -->
                @if($butuh_scorecard)
                    <div class="p-4 rounded-md border border-surface-container bg-surface-container-low/20 space-y-4">
                        <div class="flex items-center justify-between border-b border-surface-container-high/60 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-primary">Kriteria Scorecard</span>
                            <button type="button" wire:click="addKriteria" class="inline-flex items-center gap-1 text-[11px] font-bold text-primary hover:underline">
                                <span class="material-symbols-outlined text-[14px]">add</span>
                                <span>Tambah Kriteria</span>
                            </button>
                        </div>

                        <!-- General Errors -->
                        @error('scorecardKriteria')
                            <p class="text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                        @error('totalBobot')
                            <p class="text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror

                        <div class="space-y-3">
                            @foreach($scorecardKriteria as $index => $item)
                                <div class="flex gap-3 items-start">
                                    <div class="flex-1">
                                        <input type="text" wire:model.blur="scorecardKriteria.{{ $index }}.kriteria" 
                                               placeholder="Kriteria (misal: Komunikasi, Skill Laravel)"
                                               class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md text-xs text-on-surface">
                                        @error('scorecardKriteria.'.$index.'.kriteria')
                                            <span class="text-[10px] text-error font-semibold block mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="w-24 relative">
                                        <input type="number" min="1" max="100" wire:model.blur="scorecardKriteria.{{ $index }}.bobot" 
                                               placeholder="Bobot"
                                               class="w-full pl-3 pr-6 h-10 bg-surface-container-low border border-surface-container rounded-md text-xs text-on-surface">
                                        <span class="absolute right-2 top-1/2 -translate-y-1/2 text-[10px] font-bold text-on-surface-variant/40">%</span>
                                        @error('scorecardKriteria.'.$index.'.bobot')
                                            <span class="text-[10px] text-error font-semibold block mt-0.5">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    @if(count($scorecardKriteria) > 1)
                                        <button type="button" wire:click="removeKriteria({{ $index }})" class="p-2 text-error hover:bg-error/10 rounded-md">
                                            <span class="material-symbols-outlined text-[18px]">delete</span>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Penjadwalan Template Section -->
                @if($butuh_jadwal)
                    <div class="p-4 rounded-md border border-surface-container bg-surface-container-low/20 space-y-4">
                        <div class="border-b border-surface-container-high/60 pb-2">
                            <span class="text-xs font-bold uppercase tracking-wider text-primary">Konfigurasi Penjadwalan</span>
                        </div>

                        <!-- Tipe Wawancara Select -->
                        <div>
                            <label for="tipe_wawancara" class="block font-bold text-[10px] uppercase tracking-wider text-on-surface-variant mb-1.5">Tipe Wawancara <span class="text-error">*</span></label>
                            <select id="tipe_wawancara" wire:model.live="tipe_wawancara" 
                                    class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md text-xs text-on-surface cursor-pointer">
                                <option value="online">Online</option>
                                <option value="offline">Offline (On-site)</option>
                                <option value="hybrid">Hybrid</option>
                            </select>
                            @error('tipe_wawancara')
                                <span class="text-[10px] text-error font-semibold block mt-0.5">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Lokasi Default (Offline/Hybrid) -->
                        @if($tipe_wawancara === 'offline' || $tipe_wawancara === 'hybrid')
                            <div>
                                <label for="lokasi_default" class="block font-bold text-[10px] uppercase tracking-wider text-on-surface-variant mb-1.5">Lokasi / Ruangan Default <span class="text-error">*</span></label>
                                <input type="text" id="lokasi_default" wire:model.blur="lokasi_default" 
                                       placeholder="misal: Ruang Rapat Lt. 2, Kantor Cabang Jakarta"
                                       class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md text-xs text-on-surface">
                                @error('lokasi_default')
                                    <span class="text-[10px] text-error font-semibold block mt-0.5">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif

                        <!-- Tautan Virtual Default (Online/Hybrid) -->
                        @if($tipe_wawancara === 'online' || $tipe_wawancara === 'hybrid')
                            <div>
                                <label for="tautan_virtual_default" class="block font-bold text-[10px] uppercase tracking-wider text-on-surface-variant mb-1.5">Tautan Meeting Virtual Default (Opsional)</label>
                                <input type="text" id="tautan_virtual_default" wire:model.blur="tautan_virtual_default" 
                                       placeholder="misal: https://meet.google.com/abc-defg-hij"
                                       class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md text-xs text-on-surface">
                                @error('tautan_virtual_default')
                                    <span class="text-[10px] text-error font-semibold block mt-0.5">{{ $message }}</span>
                                @enderror
                            </div>
                        @endif
                    </div>
                @endif

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-surface-container-high/50">
                    <button type="button" @click="show = false" 
                            class="px-5 h-12 border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.18)] text-sm">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
