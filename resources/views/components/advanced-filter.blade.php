@props([
    'searchPlaceholder' => 'Cari...',
    'searchModel' => 'search',
])

<div class="bg-surface-container-lowest rounded-md p-4 md:p-6 shadow-sm mb-6 border border-surface-container">
    <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
        
        {{-- Search Input --}}
        <div class="relative flex-grow min-w-[240px]">
            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-on-surface-variant">search</span>
            <input
                type="text"
                wire:model.live.debounce.300ms="{{ $searchModel }}"
                placeholder="{{ $searchPlaceholder }}"
                class="w-full h-11 pl-12 pr-4 bg-surface-container-low border border-surface-container rounded-md focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-sm text-on-surface placeholder:text-on-surface-variant/70"
            />
        </div>

        <div class="flex flex-wrap items-center gap-3 flex-shrink-0">
            {{-- Filter Slot (Dropdown Accordion) --}}
            @isset($filters)
                <details class="group relative">
                    <summary class="flex items-center justify-center gap-2 h-11 px-6 bg-surface-container-low hover:bg-surface-container border border-surface-container rounded-md font-bold text-sm text-on-surface-variant group-open:text-primary group-open:border-primary/50 transition-all cursor-pointer outline-none list-none [&::-webkit-details-marker]:hidden select-none">
                        <span class="material-symbols-outlined text-[18px]">tune</span>
                        Filter
                        <span class="material-symbols-outlined text-[18px] transition-transform group-open:rotate-180">expand_more</span>
                    </summary>
                    
                    <!-- Dropdown panel -->
                    <div class="absolute right-0 left-0 sm:left-auto sm:right-0 top-[calc(100%+0.5rem)] w-full sm:w-72 p-5 bg-surface-container-lowest border border-surface-container-high/60 rounded-lg shadow-lg z-50 flex flex-col gap-4">
                        <div class="flex items-center justify-between border-b border-surface-container-high/50 pb-3 mb-1">
                            <span class="font-bold text-on-surface text-sm uppercase tracking-wider">Filter Data</span>
                            <span class="material-symbols-outlined text-on-surface-variant text-[18px]">filter_list</span>
                        </div>
                        
                        {{ $filters }}
                    </div>
                </details>
            @endisset

            {{-- Action Button Slot --}}
            @isset($actionButton)
                <div class="flex-shrink-0">
                    {{ $actionButton }}
                </div>
            @endisset
        </div>
    </div>
</div>