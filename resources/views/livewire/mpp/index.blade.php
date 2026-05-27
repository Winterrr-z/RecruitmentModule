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
            <h2 class="font-headline-lg text-headline-lg text-on-surface">List Manpower Planning</h2>
            <p class="font-body-md text-body-md text-on-surface-variant/70">Perencanaan kebutuhan tenaga kerja</p>
        </div>
        <a href="{{ route('mpp.create') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span>Tambah Planning</span>
        </a>
    </div>

    <!-- Search & Filter Controls -->
    <div class="mb-8 p-6 bg-surface-container-lowest rounded-md shadow-[0px_40px_40px_-20px_rgba(107,56,212,0.04)] border border-surface-container/30 flex flex-col sm:flex-row gap-4">
        <!-- Search Input -->
        <div class="relative flex-1">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant text-[20px]">search</span>
            <input wire:model.live.debounce.300ms="search" 
                   type="text" 
                   placeholder="Cari berdasarkan nama plan, jabatan, atau departemen..." 
                   class="w-full pl-12 pr-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface">
        </div>
        
        <!-- Department Filter -->
        <div class="w-full sm:w-64">
            <select wire:model.live="selectedDepartment" 
                    class="w-full px-6 h-12 bg-surface-container-low border-none rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md text-on-surface cursor-pointer">
                <option value="">Semua Departemen</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept }}">{{ $dept }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <!-- Cards Grid -->
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
                <p class="text-label-sm font-label-sm text-on-surface-variant max-w-md mb-6">
                    Mulai rencanakan kebutuhan tenaga kerja baru untuk departemen Anda dengan membuat manpower plan pertama.
                </p>
                <a href="{{ route('mpp.create') }}" class="inline-flex items-center justify-center gap-2 px-6 h-12 bg-primary text-white font-bold rounded-md hover:bg-primary-container transition-all active:scale-95 shadow-[0_4px_12px_rgba(107,56,212,0.2)]">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    <span>Buat Plan Pertama</span>
                </a>
            </div>
        @endif
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($mpps as $mpp)
                <div class="group relative bg-surface-container-lowest p-8 rounded-md shadow-[0px_40px_60px_-15px_rgba(107,56,212,0.06)] hover:shadow-[0px_40px_80px_-15px_rgba(107,56,212,0.1)] hover:-translate-y-1 transition-all duration-300 text-on-surface">
                    @php $badge = $mpp->getStatusBadge(); @endphp
                    <div class="absolute top-6 right-6 flex items-center gap-2 px-3 py-1 {{ $badge['bg'] }} {{ $badge['color'] }} rounded-md text-[11px] font-bold z-10">
                        <span class="w-2 h-2 {{ $badge['dotColor'] }} rounded-full animate-pulse"></span>
                        {{ $badge['label'] }}
                    </div>
                    <div class="mb-6">
                        <div class="flex items-center gap-3 mb-3">
                            @if(strtolower($mpp->status) === 'approved')
                                <span class="px-2 py-1 bg-green-100 text-green-800 text-[10px] font-bold rounded uppercase">Approved</span>
                            @elseif(strtolower($mpp->status) === 'closed')
                                <span class="px-2 py-1 bg-gray-200 text-gray-700 text-[10px] font-bold rounded uppercase">Closed</span>
                            @else
                                <span class="px-2 py-1 bg-surface-container-highest text-on-surface-variant text-[10px] font-bold rounded uppercase">Draft</span>
                            @endif
                        </div>
                        <h4 class="text-title-md font-title-md text-on-surface group-hover:text-primary transition-colors">
                            <a href="{{ route('mpp.show', $mpp->id) }}" class="after:absolute after:inset-0">
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
                            <span class="text-label-sm font-label-sm font-bold text-on-surface">
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
                    @php $computedStatus = $mpp->getComputedStatus(); @endphp
                    @if($computedStatus !== 'Closed' && $computedStatus !== 'Filled')
                        <div class="absolute bottom-6 right-6 opacity-0 group-hover:opacity-100 transition-opacity flex gap-2 z-10">
                            <a href="{{ route('mpp.edit', $mpp->id) }}" class="p-2 hover:bg-surface-container-low rounded-md transition-colors text-on-surface-variant block">
                                <span class="material-symbols-outlined text-[20px]">edit</span>
                            </a>
                            <button wire:click="delete({{ $mpp->id }})" wire:confirm="Apakah Anda yakin ingin menghapus manpower plan ini?" class="p-2 hover:bg-error/10 rounded-md transition-colors text-error">
                                <span class="material-symbols-outlined text-[20px]">delete</span>
                            </button>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</div>
