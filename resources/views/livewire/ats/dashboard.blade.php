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

    <!-- Content Header & Top Controls -->
    <div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-6">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Pipeline Pelamar (ATS)</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Kelola pelamar pekerjaan dan pantau pergerakan rekrutmen</p>
        </div>
        
        <!-- Filters Row -->
        <div class="flex flex-col sm:flex-row gap-4 items-stretch sm:items-center">
            <!-- Input Kandidat Manual Button -->
            <a href="{{ route('ats.candidate.manual', $selectedLowonganId) }}" 
               class="inline-flex items-center justify-center gap-2 px-5 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)] text-sm"
               title="Input kandidat secara manual">
                <span class="material-symbols-outlined text-[20px]">person_add</span>
                <span class="whitespace-nowrap">Input Kandidat</span>
            </a>

            <!-- Lowongan Select Dropdown -->
            <div class="relative w-full sm:w-64">
                <select wire:model.live="selectedLowonganId" 
                        class="w-full px-4 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface cursor-pointer">
                    <option value="">Semua Lowongan</option>
                    @foreach($lowongans as $job)
                        <option value="{{ $job->id }}">{{ $job->jabatan }} ({{ $job->departemen }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Search Input -->
            <div class="relative w-full sm:w-64">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
                <input wire:model.live.debounce.300ms="search" 
                       type="text" 
                       placeholder="Cari nama atau email..." 
                       class="w-full pl-12 pr-6 h-12 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface">
            </div>
        </div>
    </div>

    <!-- Pipeline Stage Horizontal Pills -->
    <div class="flex flex-wrap gap-2 mb-6 pb-2 border-b border-surface-container-high/40">
        @foreach($stages as $stage)
            @php
                $isActive = $selectedStageId == $stage->id;
                $count = $stageCounts[$stage->id] ?? 0;
            @endphp
            <button wire:click="selectStage({{ $stage->id }})" 
                    class="px-5 py-2.5 rounded-full cursor-pointer transition-all duration-200 flex items-center gap-2 border font-semibold text-sm
                    {{ $isActive 
                        ? 'bg-primary text-white border-primary shadow-[0_4px_12px_rgba(107,56,212,0.2)]' 
                        : 'bg-surface-container-lowest text-on-surface-variant border-surface-container-high/40 hover:bg-surface-container/60' }}">
                <span>{{ $stage->nama }}</span>
                <span class="px-2 py-0.5 rounded-full text-xs {{ $isActive ? 'bg-white/20 text-white' : 'bg-surface-container text-on-surface-variant/80' }}">
                    {{ $count }}
                </span>
            </button>
        @endforeach
    </div>

    <!-- Candidate List Table Container with Loading State -->
    <div class="relative min-h-[300px]">
        <!-- Loading overlay -->
        <div wire:loading.delay.longer class="absolute inset-0 bg-white/50 dark:bg-surface/50 backdrop-blur-xs flex items-center justify-center z-50 rounded-lg">
            <div class="flex items-center gap-3 px-5 py-3 bg-surface-container-lowest text-primary font-bold rounded-lg border border-surface-container-high shadow-lg">
                <svg class="animate-spin h-5 w-5 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="text-sm font-semibold">Memuat data...</span>
            </div>
        </div>

    <div class="bg-surface-container-lowest rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] border border-surface-container/30 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-surface-container-high bg-surface-container-low/40">
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Kandidat</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Email</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Status</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Lowongan</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Tanggal Melamar</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Pindah Tahap</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container/30">
                    @forelse($candidates as $candidate)
                        <tr class="hover:bg-surface/30 transition-colors group">
                            <!-- Nama Kandidat -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('ats.candidate.detail', ['candidateId' => $candidate->id]) }}" class="flex items-center gap-3 group/item">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm group-hover/item:bg-primary group-hover/item:text-white transition-colors">
                                        {{ strtoupper(substr($candidate->nama, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-title-md text-sm font-bold text-on-surface group-hover/item:text-primary transition-colors">
                                            {{ $candidate->nama }}
                                        </span>
                                        <div class="text-[11px] text-on-surface-variant/65">ID: #{{ $candidate->id }}</div>
                                    </div>
                                </a>
                            </td>
                            <!-- Email -->
                            <td class="px-6 py-4 whitespace-nowrap font-body-md text-sm text-on-surface-variant/80">
                                {{ $candidate->email }}
                            </td>
                            
                            <!-- Status Badge -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($candidate->status === 'Ditolak')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-error/10 text-error border border-error/20">
                                        <span class="w-1.5 h-1.5 bg-error rounded-full"></span>
                                        Ditolak
                                    </span>
                                @elseif($candidate->status === 'Applied')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/10 text-blue-700 border border-blue-500/20">
                                        <span class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></span>
                                        Applied
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-700 border border-green-500/20">
                                        <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                        {{ $candidate->status }}
                                    </span>
                                @endif
                            </td>
                            
                            <!-- Lowongan -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-body-md text-sm text-on-surface font-semibold">{{ $candidate->lowongan?->jabatan ?: 'Kandidat Mandiri' }}</span>
                                <div class="text-[11px] text-on-surface-variant/60">{{ $candidate->lowongan?->departemen ?: 'Tanpa Lowongan' }}</div>
                            </td>
                            
                            <!-- Tanggal Melamar -->
                            <td class="px-6 py-4 whitespace-nowrap font-body-md text-sm text-on-surface-variant/80">
                                {{ $candidate->created_at->format('d M Y') }}
                            </td>
                            
                            <!-- Pindah Tahap Dropdown -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <select onchange="confirm('Apakah Anda yakin ingin memindahkan kandidat ini ke stage yang dipilih?') ? @this.moveCandidate({{ $candidate->id }}, this.value) : this.selectedIndex = 0"
                                        class="px-3 h-10 bg-surface-container border border-surface-container-high rounded-md focus:ring-2 focus:ring-primary/20 text-xs text-on-surface font-semibold cursor-pointer max-w-[150px]">
                                    <option value="" disabled selected>Pilih Stage...</option>
                                    @foreach($stages as $stageOption)
                                        @if($stageOption->id != $candidate->current_stage_id)
                                            <option value="{{ $stageOption->id }}">{{ $stageOption->nama }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </td>

                            <!-- Aksi Buttons -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Reject Action -->
                                    <button wire:click="reject({{ $candidate->id }})" 
                                            wire:confirm="Apakah Anda yakin ingin menolak kandidat {{ $candidate->nama }}?"
                                            class="p-2 hover:bg-error/10 text-error rounded-md transition-colors" 
                                            title="Reject (Tolak)">
                                        <span class="material-symbols-outlined text-[20px]">cancel</span>
                                    </button>

                                    <!-- Blacklist Action -->
                                    <button wire:click="confirmBlacklist({{ $candidate->id }})" 
                                            class="p-2 hover:bg-black/10 text-on-surface rounded-md transition-colors" 
                                            title="Blacklist (Daftar Hitam)">
                                        <span class="material-symbols-outlined text-[20px]">block</span>
                                    </button>

                                    <!-- Approve / Lanjutkan Action -->
                                    <button wire:click="approve({{ $candidate->id }})" 
                                            wire:confirm="Apakah Anda yakin ingin memindahkan kandidat {{ $candidate->nama }} ke tahap berikutnya?"
                                            class="p-2 hover:bg-green-500/10 text-green-600 rounded-md transition-colors" 
                                            title="Lanjutkan Tahap (Approve)">
                                        <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-on-surface-variant/50">
                                Tidak ada kandidat pada tahap seleksi ini yang sesuai dengan filter pencarian.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($candidates->hasPages())
            <div class="px-6 py-4 border-t border-surface-container bg-surface-container-low/20">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
    </div>

    <!-- Blacklist Confirmation Modal -->
    <div x-data="{ show: @entangle('showBlacklistModal') }" 
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
             class="relative bg-surface-container-lowest rounded-md w-full max-w-md p-8 mx-4 shadow-[0_24px_48px_-12px_rgba(107,56,212,0.18)] border border-surface-container-high/50 z-10">
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-surface-container-high/50">
                <div class="flex items-center gap-2 text-error">
                    <span class="material-symbols-outlined text-[24px]">block</span>
                    <h3 class="text-title-md font-headline-lg">Blacklist Kandidat</h3>
                </div>
                <button @click="show = false" class="text-on-surface-variant/60 hover:text-on-surface transition-colors">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <!-- Modal Content (Form) -->
            <form wire:submit.prevent="blacklist" class="space-y-6">
                <div>
                    <label for="alasan" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Alasan Masuk Daftar Hitam <span class="text-error">*</span></label>
                    <textarea id="alasan" wire:model="blacklistAlasan" rows="4"
                              placeholder="Berikan penjelasan atau pelanggaran/alasan kandidat ini dimasukkan ke dalam daftar hitam (tidak dapat melamar lagi)..."
                              class="w-full p-4 bg-surface-container-low border border-surface-container focus:border-primary/55 rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface 
                              @error('blacklistAlasan') border-error @enderror"></textarea>
                    @error('blacklistAlasan')
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
                        Blacklist &amp; Tolak
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
