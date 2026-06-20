<div>
    <x-breadcrumb :items="[['label' => 'ATS', 'url' => null], ['label' => 'All Candidates', 'url' => null]]" />
    <!-- Content Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">Candidate List</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">
                Menampilkan {{ $candidates->total() }} kandidat
            </p>
        </div>
        <div>
            <a href="{{ route('ats.blacklist') }}" 
               class="inline-flex items-center gap-2 px-4 py-2 bg-surface-container hover:bg-surface-container-highest text-on-surface-variant rounded-full transition-colors">
                <span class="material-symbols-outlined text-[20px]">block</span>
                <span class="font-label-sm">Manage Blacklist</span>
            </a>
        </div>
    </div>

    <x-advanced-filter searchPlaceholder="Cari nama atau email..." searchModel="search">
        <x-slot:filters>
            <!-- Filter Lowongan -->
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Lowongan</label>
                <select wire:model.live="filterVacancy" class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Lowongan</option>
                    @foreach($vacancies as $job)
                        <option value="{{ $job->id }}">{{ $job->job_title }} ({{ $job->department }})</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Status</label>
                <select wire:model.live="filterStatus" class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Applied">Applied</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Offered">Offered</option>
                    <option value="Hired">Hired</option>
                    <option value="Rejected">Rejected</option>
                    <option value="Withdrawn">Withdrawn</option>
                    <option value="Expired">Expired</option>
                </select>
            </div>

            <!-- Filter Stage -->
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Stage</label>
                <select wire:model.live="filterStage" class="w-full px-3 h-10 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Stage</option>
                    @foreach($stages as $stage)
                        <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                    @endforeach
                </select>
            </div>
        </x-slot:filters>
    </x-advanced-filter>

    <!-- Candidate List Table Container with Loading State -->
    <div class="relative min-h-[300px]">
        <!-- Loading overlay -->
        <div wire:loading.delay class="absolute inset-0 bg-white/50 dark:bg-surface/50 backdrop-blur-xs flex items-center justify-center z-50 rounded-lg">
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
                <table class="w-full text-sm text-left text-on-surface border-collapse">
                    <thead>
                        <tr class="border-b border-surface-container-high bg-surface-container-low/40">
                            <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Nama</th>
                            <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Email</th>
                            <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Lowongan</th>
                            <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Stage</th>
                            <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Status</th>
                            <th class="px-6 py-4 font-bold text-label-sm uppercase tracking-wider text-on-surface-variant">Tanggal Melamar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container/30">
                        @forelse($candidates as $candidate)
                            <tr wire:key="candidate-{{ $candidate->id }}" x-data @click="window.location.href='{{ route('ats.candidate.detail', ['candidateId' => $candidate->id, 'from' => 'candidates']) }}'" class="even:bg-white odd:bg-gray-50 hover:bg-surface-container-low/80 transition-colors group cursor-pointer">
                                <!-- Nama -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-primary/10 text-primary flex items-center justify-center font-bold text-sm">
                                            {{ strtoupper(substr($candidate->name, 0, 2)) }}
                                        </div>
                                        <span class="font-title-md text-sm font-bold text-on-surface">
                                            {{ $candidate->name }}
                                        </span>
                                    </div>
                                </td>

                                <!-- Email -->
                                <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant/85 font-medium">
                                    {{ $candidate->email }}
                                </td>

                                <!-- Vacancy -->
                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                    {{ $candidate->vacancy->job_title ?? '-' }}
                                </td>

                                <!-- Stage -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-surface-container-high text-on-surface-variant border border-surface-container">
                                        {{ $candidate->currentStage->name ?? '-' }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @switch($candidate->status->value)
                                        @case('Applied')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-500/10 text-blue-700 border border-blue-500/20">
                                                <span class="w-1.5 h-1.5 bg-blue-600 rounded-full"></span>
                                                Applied
                                            </span>
                                            @break
                                        @case('Hired')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-500/10 text-green-700 border border-green-500/20">
                                                <span class="w-1.5 h-1.5 bg-green-600 rounded-full"></span>
                                                Hired
                                            </span>
                                            @break
                                        @case('Ditolak')
                                        @case('Rejected')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-700 border border-red-500/20">
                                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                                Ditolak
                                            </span>
                                            @break
                                        @case('Withdrawn')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-500/10 text-red-700 border border-red-500/20">
                                                <span class="w-1.5 h-1.5 bg-red-600 rounded-full"></span>
                                                Withdrawn
                                            </span>
                                            @break
                                        @case('Offering Expired')
                                        @case('Expired')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-surface-container-high text-on-surface-variant border border-surface-container">
                                                <span class="w-1.5 h-1.5 bg-on-surface-variant/50 rounded-full"></span>
                                                Offering Expired
                                            </span>
                                            @break
                                        @default
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-surface-container-high text-on-surface-variant border border-surface-container">
                                                <span class="w-1.5 h-1.5 bg-on-surface-variant/50 rounded-full"></span>
                                                {{ $candidate->status->value ?? $candidate->status }}
                                            </span>
                                    @endswitch
                                </td>

                                <!-- Tanggal Melamar -->
                                <td class="px-6 py-4 whitespace-nowrap text-on-surface-variant/85 font-medium">
                                    {{ $candidate->created_at->format('d M Y') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-on-surface-variant/50">
                                    Tidak ada kandidat ditemukan.
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
</div>