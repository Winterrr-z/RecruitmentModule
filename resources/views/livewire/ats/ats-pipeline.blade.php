<div>
    <x-breadcrumb :items="[['label' => 'ATS', 'url' => null], ['label' => 'Pipeline', 'url' => null]]" />
    <x-toast-alert />

    <!-- Content Header & Top Controls -->
    <div class="mb-8 flex flex-col md:flex-row justify-between md:items-center gap-6">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Stage Pipeline</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Kelola pelamar pekerjaan dan pantau pergerakan rekrutmen</p>
        </div>
        <a href="{{ route('ats.candidate.manual', $selectedVacancyId) }}" 
           class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
            <span class="material-symbols-outlined text-[20px]">person_add</span>
            <span>Input Kandidat</span>
        </a>
    </div>

    <!-- Pipeline Stage Chevron Navigation -->
    <div class="mb-8 w-full overflow-x-auto pb-4 custom-scrollbar">
        <div class="flex items-center min-w-max gap-1 p-1">
            @foreach($stages as $index => $stage)
                @php
                    $isActive = $selectedStageId == $stage->id;
                    $count = $stageCounts[$stage->id] ?? 0;
                    
                    if ($loop->first && $loop->last) {
                        $polygon = 'polygon(0 0, 100% 0, 100% 100%, 0 100%)';
                        $pl = 'pl-6';
                        $pr = 'pr-6';
                    } elseif ($loop->first) {
                        $polygon = 'polygon(0 0, calc(100% - 1.25rem) 0, 100% 50%, calc(100% - 1.25rem) 100%, 0 100%)';
                        $pl = 'pl-6';
                        $pr = 'pr-9';
                    } elseif ($loop->last) {
                        $polygon = 'polygon(0 0, 100% 0, 100% 100%, 0 100%, 1.25rem 50%)';
                        $pl = 'pl-9';
                        $pr = 'pr-6';
                    } else {
                        $polygon = 'polygon(0 0, calc(100% - 1.25rem) 0, 100% 50%, calc(100% - 1.25rem) 100%, 0 100%, 1.25rem 50%)';
                        $pl = 'pl-9';
                        $pr = 'pr-9';
                    }

                    $colors = [
                        '#6b38d4', '#fd933d', '#10b981', '#3b82f6', '#ec4899', '#f59e0b', '#8b5cf6', '#14b8a6',
                        '#f43f5e', '#06b6d4', '#6366f1', '#84cc16', '#d946ef', '#0284c7', '#f97316', '#22c55e',
                        '#eab308', '#a855f7', '#fb7185', '#475569'
                    ];
                    $stageColor = $colors[$index % count($colors)];
                @endphp
                <button wire:click="selectStage({{ $stage->id }})" 
                        class="relative h-12 flex items-center justify-center gap-2 {{ $pl }} {{ $pr }} text-sm font-bold transition-all duration-450
                        {{ $isActive 
                            ? 'text-white scale-[1.15] shadow-md z-10' 
                            : 'bg-surface-container-low text-on-surface-variant hover:bg-surface-container-high hover:text-on-surface hover:scale-[1.05] hover:z-20 hover:shadow-md z-0' }}"
                        style="clip-path: {{ $polygon }}; @if($isActive) background-color: {{ $stageColor }}; @endif">
                    <span class="whitespace-nowrap">{{ $stage->name }}</span>
                    <span class="flex items-center justify-center min-w-[20px] h-[20px] px-1.5 rounded-full text-[10px]"
                          style="@if($isActive) background-color: rgba(255, 255, 255, 0.2); color: white; @else background-color: {{ $stageColor }}1a; color: {{ $stageColor }}; @endif">
                        {{ $count }}
                    </span>
                </button>
            @endforeach
        </div>
    </div>
    <x-advanced-filter searchPlaceholder="Cari kandidat di pipeline..." searchModel="search">
        <x-slot:filters>
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Vacancy</label>
                <select wire:model.live="selectedVacancyId" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Vacancy</option>
                    @foreach($vacancies as $job)
                        <option value="{{ $job->id }}">{{ $job->job_title }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-advanced-filter>
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
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant sticky left-0 z-10 bg-gray-50 shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">Kandidat</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Email</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Status</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Vacancy</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Tanggal Melamar</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Pindah Tahap</th>
                        <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container/30">
                    @forelse($candidates as $candidate)
                        <tr wire:key="candidate-{{ $candidate->id }}" x-data @click="window.location.href='{{ route('ats.candidate.detail', ['candidateId' => $candidate->id]) }}'" class="even:bg-white odd:bg-gray-50 hover:bg-surface-container-low/80 transition-colors group cursor-pointer">
                            <!-- Nama Kandidat -->
                            <td class="px-6 py-4 whitespace-nowrap sticky left-0 z-10 bg-inherit shadow-[2px_0_5px_-2px_rgba(0,0,0,0.05)]">
                                <a href="{{ route('ats.candidate.detail', ['candidateId' => $candidate->id]) }}" class="flex items-center gap-3 group/item">
                                    <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm group-hover/item:bg-primary group-hover/item:text-white transition-colors">
                                        {{ strtoupper(substr($candidate->name, 0, 2)) }}
                                    </div>
                                    <div>
                                        <span class="font-title-md text-sm font-bold text-on-surface group-hover/item:text-primary transition-colors">
                                            {{ $candidate->name }}
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
                                @switch($candidate->status->value)
                                    @case('Rejected')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-950/20 text-red-950 border border-red-950/30">
                                            <span class="w-1.5 h-1.5 bg-red-900 rounded-full"></span>
                                            Rejected
                                        </span>
                                        @break
                                    @case('Applied')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/10 text-blue-700 border border-blue-500/20">
                                            <span class="w-1.5 h-1.5 bg-blue-600 rounded-full animate-pulse"></span>
                                            Applied
                                        </span>
                                        @break
                                    @case('In Progress')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-surface-container-high text-on-surface-variant border border-surface-container">
                                            <span class="w-1.5 h-1.5 bg-on-surface-variant/50 rounded-full"></span>
                                            In Progress
                                        </span>
                                        @break
                                    @case('Offered')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-500/10 text-amber-700 border border-amber-500/20">
                                            <span class="w-1.5 h-1.5 bg-amber-600 rounded-full"></span>
                                            Offered
                                        </span>
                                        @break
                                    @case('Hired')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-700 border border-green-500/20">
                                            <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                            Hired
                                        </span>
                                        @break
                                    @case('Declined')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-700 border border-red-500/20">
                                            <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                            Declined
                                        </span>
                                        @break
                                    @case('Expired')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-700 border border-red-500/20">
                                            <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                            Expired
                                        </span>
                                        @break
                                    @default
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-surface-container-high text-on-surface-variant border border-surface-container">
                                            <span class="w-1.5 h-1.5 bg-on-surface-variant/50 rounded-full"></span>
                                            {{ $candidate->status->value ?? $candidate->status }}
                                        </span>
                                @endswitch
                            </td>
                            
                            <!-- Vacancy -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="font-body-md text-sm text-on-surface font-semibold">{{ $candidate->vacancy?->job_title ?: 'Kandidat Mandiri' }}</span>
                                <div class="text-[11px] text-on-surface-variant/60">{{ $candidate->vacancy?->department ?: 'Tanpa Vacancy' }}</div>
                            </td>
                            
                            <!-- Tanggal Melamar -->
                            <td class="px-6 py-4 whitespace-nowrap font-body-md text-sm text-on-surface-variant/80">
                                {{ $candidate->created_at->format('d M Y') }}
                            </td>
                            
                            <!-- Pindah Tahap Dropdown -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-on-surface-variant">
                                @if($candidate->current_stage_id == 2 || strtolower($candidate->currentStage?->name) === 'final')
                                    <span class="text-on-surface-variant/40">-</span>
                                @else
                                    <select @click.stop onchange="confirm('Apakah Anda yakin ingin memindahkan kandidat ini ke stage yang dipilih?') ? @this.moveCandidate({{ $candidate->id }}, this.value) : this.selectedIndex = 0"
                                            class="px-3 h-10 bg-surface-container border border-surface-container-high rounded-md focus:ring-2 focus:ring-primary/20 text-xs text-on-surface font-semibold cursor-pointer max-w-[150px]">
                                        <option value="" disabled selected>Pilih Stage...</option>
                                        @foreach($stages as $stageOption)
                                            @if($stageOption->id != $candidate->current_stage_id)
                                                <option value="{{ $stageOption->id }}">{{ $stageOption->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                @endif
                            </td>

                            <!-- Aksi Buttons -->
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                @if(in_array($candidate->status, [\App\Enums\CandidateStatus::HIRED, \App\Enums\CandidateStatus::REJECTED, \App\Enums\CandidateStatus::DECLINED, \App\Enums\CandidateStatus::EXPIRED, \App\Enums\CandidateStatus::BLACKLISTED]) || ($candidate->current_stage_id == 2 && $candidate->status !== \App\Enums\CandidateStatus::IN_PROGRESS))
                                    <span class="text-on-surface-variant/40 pr-6">-</span>
                                @else
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Reject Action -->
                                        <button @click.stop wire:click="reject({{ $candidate->id }})" 
                                                wire:confirm="Apakah Anda yakin ingin menolak kandidat {{ $candidate->name }}?"
                                                class="p-2 hover:bg-error/10 text-error rounded-md transition-colors" 
                                                title="Reject (Tolak)">
                                            <span class="material-symbols-outlined text-[20px]">cancel</span>
                                        </button>

                                        <!-- Blacklist Action -->
                                        <button @click.stop wire:click="confirmBlacklist({{ $candidate->id }})" 
                                                class="p-2 hover:bg-black/10 text-on-surface rounded-md transition-colors" 
                                                title="Blacklist (Daftar Hitam)">
                                            <span class="material-symbols-outlined text-[20px]">block</span>
                                        </button>

                                        <!-- Approve / Hired Action -->
                                        <button @click.stop wire:click="approve({{ $candidate->id }})" 
                                                wire:confirm="Apakah Anda yakin ingin meng-hire kandidat {{ $candidate->name }}?"
                                                class="p-2 hover:bg-green-500/10 text-green-600 rounded-md transition-colors" 
                                                title="Hired">
                                            <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                        </button>
                                    </div>
                                @endif
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
                    <label for="reason" class="block font-bold text-label-sm uppercase tracking-wider text-on-surface-variant mb-2">Alasan Masuk Daftar Hitam <span class="text-error">*</span></label>
                    <textarea id="reason" wire:model="blacklistAlasan" rows="4"
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