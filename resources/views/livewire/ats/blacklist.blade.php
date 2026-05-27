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

    <!-- Content Header & Top Controls -->
    <div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-6">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Blacklist Pelamar</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Daftar pelamar yang ditangguhkan dan diblokir dari lowongan pekerjaan</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Cari nama, email, atau telepon..." 
                       class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface">
            </div>

            <!-- Add Blacklist Button -->
            <button wire:click="openAddModal" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-error text-white font-bold rounded-md hover:bg-red-700 transition-all active:scale-95 shadow-[0_4px_12px_rgba(218,26,26,0.18)]">
                <span class="material-symbols-outlined text-[20px]">block</span>
                <span>Tambah Blacklist</span>
            </button>
        </div>
    </div>

    <!-- Blacklist List Table -->
    <div class="bg-surface-container-lowest rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-surface-container-high bg-surface-container-low/40">
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Nama</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Email</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Telepon</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Alasan Blacklist</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Tanggal Diblokir</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container/30">
                    @forelse($blacklistList as $item)
                        <tr class="hover:bg-surface/30 transition-colors group text-on-surface">
                            <!-- Nama -->
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-sm">
                                {{ $item->nama }}
                            </td>
                            <!-- Email -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant/80">
                                {{ $item->email }}
                            </td>
                            <!-- Telepon -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant/80">
                                {{ $item->telepon }}
                            </td>
                            <!-- Alasan -->
                            <td class="px-6 py-4 text-sm text-error font-semibold">
                                {{ $item->alasan }}
                            </td>
                            <!-- Tanggal -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant/60">
                                {{ $item->created_at ? $item->created_at->format('d M Y H:i') : '-' }}
                            </td>
                            <!-- Aksi -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <button wire:click="deleteBlacklist({{ $item->id }})" 
                                        wire:confirm="Apakah Anda yakin ingin menghapus '{{ $item->nama }}' dari daftar blacklist?"
                                        class="p-2 hover:bg-error/10 text-error rounded-md transition-colors" 
                                        title="Hapus dari Blacklist">
                                    <span class="material-symbols-outlined text-[20px]">delete</span>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant/50">
                                Tidak ada data blacklist pelamar yang sesuai.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($blacklistList->hasPages())
            <div class="px-6 py-4 border-t border-surface-container bg-surface-container-low/20">
                {{ $blacklistList->links() }}
            </div>
        @endif
    </div>

    <!-- Add Blacklist Modal -->
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
             class="relative bg-surface-container-lowest rounded-md w-full max-w-lg p-8 mx-4 shadow-[0_24px_48px_-12px_rgba(218,26,26,0.18)] border border-surface-container-high/50 z-10 max-h-[90vh] overflow-y-auto">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-container-high/50">
                <div class="flex items-center gap-2 text-error">
                    <span class="material-symbols-outlined text-[24px]">block</span>
                    <h3 class="text-title-md font-headline-lg">Tambah ke Daftar Blacklist</h3>
                </div>
                <button @click="show = false" class="text-on-surface-variant/60 hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Auto-fill option section: Tambah dari Kandidat -->
            <div class="mb-6 p-4 rounded-md border border-surface-container bg-surface-container-low/40">
                <div class="flex items-center justify-between gap-4 mb-2">
                    <span class="text-xs font-bold text-on-surface-variant uppercase tracking-wider">Tambah dari Kandidat Aktif</span>
                    <button type="button" wire:click="toggleCandidatePicker" class="text-xs text-primary font-bold hover:underline">
                        {{ $showCandidatePicker ? 'Sembunyikan Pencarian' : 'Cari Kandidat...' }}
                    </button>
                </div>

                @if($showCandidatePicker)
                    <div class="space-y-3">
                        <input type="text" wire:model.live.debounce.250ms="candidateSearch" 
                               placeholder="Ketik minimal 2 karakter nama/email..."
                               class="w-full px-3 h-10 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white text-xs text-on-surface">
                        
                        <!-- Search results -->
                        @if(strlen($candidateSearch) >= 2)
                            <div class="max-h-40 overflow-y-auto divide-y divide-surface-container border border-surface-container rounded-md bg-white">
                                @forelse($pickerCandidates as $cand)
                                    <button type="button" wire:click="selectCandidate({{ $cand->id }})" class="w-full text-left p-2.5 hover:bg-primary/5 transition-colors flex items-center justify-between gap-4 text-xs">
                                        <div>
                                            <span class="font-bold text-on-surface block">{{ $cand->nama }}</span>
                                            <span class="text-on-surface-variant/60 block">{{ $cand->email }}</span>
                                        </div>
                                        <span class="px-2 py-0.5 bg-surface-container text-on-surface-variant/70 rounded text-[10px] font-semibold">Pilih</span>
                                    </button>
                                @empty
                                    <div class="p-3 text-center text-xs text-on-surface-variant/50">
                                        Kandidat tidak ditemukan.
                                    </div>
                                @endforelse
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Form Inputs -->
            <form wire:submit.prevent="save" class="space-y-6">
                <!-- Nama -->
                <div>
                    <label for="nama" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nama Lengkap <span class="text-error">*</span></label>
                    <input type="text" id="nama" wire:model="nama" 
                           placeholder="Nama lengkap kandidat"
                           class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('nama') border-error @enderror">
                    @error('nama')
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
                               placeholder="email@example.com"
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
                        <label for="telepon" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Nomor Telepon <span class="text-error">*</span></label>
                        <input type="text" id="telepon" wire:model="telepon" 
                               placeholder="Nomor telepon"
                               class="w-full px-4 h-12 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('telepon') border-error @enderror">
                        @error('telepon')
                            <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">error</span>
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                </div>

                <!-- Alasan -->
                <div>
                    <label for="alasan" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Alasan Blacklist <span class="text-error">*</span></label>
                    <textarea id="alasan" wire:model="alasan" rows="4"
                              placeholder="Tuliskan alasan pemblokiran secara detail..."
                              class="w-full p-4 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface @error('alasan') border-error @enderror"></textarea>
                    @error('alasan')
                        <p class="mt-1 text-xs text-error font-semibold flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">error</span>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 pt-6 border-t border-surface-container-high/50">
                    <button type="button" @click="show = false" 
                            class="px-5 h-12 border border-outline/35 text-on-surface-variant hover:text-on-surface hover:bg-surface-container rounded-md transition-all active:scale-95 font-semibold text-sm">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-6 h-12 bg-error text-white font-bold rounded-md hover:bg-red-700 transition-all active:scale-95 shadow-[0_4px_12px_rgba(218,26,26,0.18)] text-sm">
                        Simpan Blacklist
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
