@props(['items' => []])

<nav class="flex mb-6" aria-label="Breadcrumb">
  <ol class="inline-flex items-center space-x-1 md:space-x-2 flex-wrap">
    {{-- Home --}}
    <li class="inline-flex items-center">
      <a href="{{ route('dashboard') }}" class="text-on-surface-variant hover:text-primary transition-colors">
        <span class="material-symbols-outlined text-[18px]">dashboard</span>
      </a>
    </li>
    @foreach ($items as $item)
      <li>
        <div class="flex items-center">
          <span class="material-symbols-outlined text-[16px] text-outline-variant mx-1">chevron_right</span>
          @if (!empty($item['url']))
            <a href="{{ $item['url'] }}" class="text-sm text-on-surface-variant hover:text-primary transition-colors">
              {{ $item['label'] }}
            </a>
            
          @else
            <span class="text-sm text-on-surface font-medium">{{ $item['label'] }}</span>
          @endif
          
        </div>
      </li>
    @endforeach
  </ol>
</nav>
