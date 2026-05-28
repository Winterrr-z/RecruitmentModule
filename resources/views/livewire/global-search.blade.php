<div class="relative group" id="global-search-container">
    <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant" data-icon="search">search</span>
    <input wire:model.live.debounce.300ms="query" 
           id="global-search-input"
           class="pl-12 pr-6 h-12 bg-surface-container-low border-none rounded-md w-64 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-body-md" 
           placeholder="Cari Fitur" 
           type="text" 
           autocomplete="off">

    @if(!empty($query))
    <div id="global-search-dropdown" class="absolute top-full right-0 mt-2 w-72 bg-surface-container-lowest border border-surface-container shadow-[0_20px_40px_rgba(107,56,212,0.1)] rounded-md overflow-hidden z-[100]">
        @if(count($this->searchResults) > 0)
            <div class="py-2">
                <div class="px-4 py-2 text-xs font-bold text-on-surface-variant/70 uppercase tracking-wider">Hasil Pencarian</div>
                @foreach($this->searchResults as $result)
                <a href="{{ $result['url'] }}" class="flex items-center gap-3 px-4 py-3 hover:bg-surface-container-low transition-colors no-underline group/item">
                    <div class="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center group-hover/item:bg-primary transition-colors shrink-0">
                        <span class="material-symbols-outlined text-[18px] text-primary group-hover/item:text-white">{{ $result['icon'] }}</span>
                    </div>
                    <span class="text-sm font-semibold text-on-surface">{{ $result['title'] }}</span>
                </a>
                @endforeach
            </div>
        @else
            <div class="p-6 text-center text-sm text-on-surface-variant/70 flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-[32px] opacity-50">search_off</span>
                <span>Fitur tidak ditemukan. Coba kata kunci lain.</span>
            </div>
        @endif
    </div>
    @endif

    <script>
        document.addEventListener('livewire:initialized', () => {
            document.addEventListener('click', function(event) {
                const container = document.getElementById('global-search-container');
                // Jika klik terjadi di luar komponen pencarian
                if (container && !container.contains(event.target)) {
                    // Hapus query agar dropdown tertutup
                    @this.set('query', '');
                }
            });
        });
    </script>
</div>
