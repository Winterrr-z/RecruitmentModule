<div>
    <x-breadcrumb :items="[['label' => 'Manpower Planning', 'url' => null]]" />
    <x-toast-alert />

    <!-- Content Header -->
    <div class="mb-8 flex flex-col sm:flex-row justify-between sm:items-center gap-4">
        <div>
            <h2 class="font-headline-lg text-headline-lg text-on-surface">List Manpower Planning</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Perencanaan kebutuhan tenaga kerja</p>
        </div>
        <a href="{{ route('mpp.create') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Tambah Planning</span>
        </a>
    </div>

    <!-- Search & Filter Controls -->
    <x-advanced-filter searchPlaceholder="Cari berdasarkan nama plan, jabatan, atau departemen..." searchModel="search">
        <x-slot:filters>
            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Departemen</label>
                <select wire:model.live="selectedDepartment" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Departemen</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept }}">{{ $dept }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Status</label>
                <select wire:model.live="status" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="">Semua Status</option>
                    <option value="Draft">Draft</option>
                    <option value="Approved">Approved</option>
                    <option value="Closed">Closed</option>
                    <option value="completed">Completed</option>
                </select>
            </div>

            <div>
                <label class="block font-bold text-[11px] uppercase tracking-wider text-on-surface-variant mb-1.5">Urutkan</label>
                <select wire:model.live="sortBy" class="w-full px-3 h-11 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface cursor-pointer">
                    <option value="newest">Terbaru (Latest)</option>
                    <option value="oldest">Terlama (Oldest)</option>
                </select>
            </div>
        </x-slot:filters>
    </x-advanced-filter>

    <!-- Cards Grid Container with Loading State -->
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

    @if($mpps->isEmpty())
        @if(!empty($search) || !empty($selectedDepartment))
            <!-- Search/Filter Empty State -->
            <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-md border border-dashed border-outline-variant/50 shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.04)]">
                <span class="material-symbols-outlined text-[64px] text-on-surface-variant/30 mb-4">search_off</span>
                <h3 class="text-title-md font-title-md text-on-surface mb-2">Hasil Tidak Ditemukan</h3>
                <p class="text-label-sm font-label-sm text-on-surface-variant max-w-md mb-6">
                    Tidak ada manpower plan yang cocok dengan kata pencarian atau filter departemen Anda. Coba bersihkan pencarian atau filter.
                </p>
                <button wire:click="resetFilters" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
                    <span class="material-symbols-outlined text-[20px]">refresh</span>
                    <span>Reset Filter &amp; Pencarian</span>
                </button>
            </div>
        @else
            <!-- Original Database Empty State -->
            <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-md border border-dashed border-outline-variant/50 shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.04)]">
                <span class="material-symbols-outlined text-[64px] text-on-surface-variant/30 mb-4">group_add</span>
                <h3 class="text-title-md font-title-md text-on-surface mb-2">Belum Ada Manpower Planning</h3>
            </div>
        @endif
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($mpps as $mpp)
                @php 
                    $computedStatus = $mpp->getComputedStatus();
                    $isCompleted = $computedStatus === 'Closed' || $computedStatus === 'Completed';
                @endphp
                <div class="group relative p-8 rounded-md border transition-all duration-300 text-on-surface
                    {{ $isCompleted 
                        ? 'bg-surface-container-low border-surface-container/60 opacity-70 grayscale shadow-none' 
                        : 'bg-surface-container-lowest border-surface-container/30 shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] hover:shadow-[0px_40px_80px_-15px_rgba(107,56,212,0.1)] hover:-translate-y-1' }}">
                    @if($computedStatus && !in_array($computedStatus, ['Closed', 'Completed']))
                        <x-mpp-status-badge :status="$computedStatus" class="absolute top-6 right-6 z-10" />
                    @endif
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-3">
                            @if($mpp->status === \App\Enums\MppStatus::APPROVED)
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold rounded uppercase">Approved</span>
                            @elseif($mpp->status === \App\Enums\MppStatus::CLOSED)
                                <span class="px-2 py-1 bg-gray-200 text-gray-700 text-[10px] font-bold rounded uppercase">Closed</span>
                            @else
                                <span class="px-2 py-1 bg-surface-container-highest text-on-surface-variant text-[10px] font-bold rounded uppercase">Draft</span>
                            @endif
                        </div>
                        <h4 class="text-title-md font-title-md text-on-surface group-hover:text-primary transition-colors">
                            <a href="{{ route('mpp.show', $mpp->id) }}" class="after:absolute after:inset-0">
                                {{ $mpp->plan_name }}
                            </a>
                        </h4>
                        <p class="text-label-sm font-label-sm text-on-surface-variant">
                            {{ $mpp->department }}
                        </p>
                    </div>
                    <div class="space-y-4 pt-4 border-t border-surface-container">
                        <div class="flex justify-between items-center">
                            <span class="text-label-sm font-label-sm text-on-surface-variant">Kuota:</span>
                            <span class="text-label-sm font-label-sm font-bold text-on-surface">
                                {{ $mpp->quota }} Orang
                            </span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-label-sm font-label-sm text-on-surface-variant">Estimasi Gaji:</span>
                            <span class="text-label-sm font-label-sm font-bold text-primary">
                                @if($mpp->estimated_salary_min && $mpp->estimated_salary_max)
                                    Rp {{ number_format($mpp->estimated_salary_min, 0, ',', '.') }} - Rp {{ number_format($mpp->estimated_salary_max, 0, ',', '.') }}
                                @elseif($mpp->estimated_salary_min)
                                    Rp {{ number_format($mpp->estimated_salary_min, 0, ',', '.') }}
                                @elseif($mpp->estimated_salary_max)
                                    Rp {{ number_format($mpp->estimated_salary_max, 0, ',', '.') }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                    </div>
                    <!-- Action Menu -->
                    @php $computedStatus = $mpp->getComputedStatus(); @endphp
                    @if($computedStatus !== 'Closed' && $computedStatus !== 'Completed')
                        <div class="absolute bottom-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-10">
                            <a href="{{ route('mpp.edit', $mpp->id) }}" class="p-2 hover:bg-surface-container-low rounded-md transition-colors text-on-surface-variant block">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($mpps->hasPages())
            <div class="mt-8 px-2">
                {{ $mpps->links() }}
            </div>
        @endif
    @endif
    </div>
</div>