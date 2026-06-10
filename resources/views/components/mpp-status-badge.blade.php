@props(['status'])

@php
    $badge = match ($status) {
        'In Progress' => [
            'label' => 'In Progress',
            'color' => 'text-blue-700',
            'bg' => 'bg-blue-100',
            'dotColor' => 'bg-blue-500',
            'icon' => 'sync',
        ],
        'Need Attention' => [
            'label' => 'Need Attention',
            'color' => 'text-yellow-800',
            'bg' => 'bg-yellow-100',
            'dotColor' => 'bg-yellow-500',
            'icon' => 'warning',
        ],
        'Urgent' => [
            'label' => 'Urgent',
            'color' => 'text-orange-800',
            'bg' => 'bg-orange-100',
            'dotColor' => 'bg-orange-500',
            'icon' => 'priority_high',
        ],
        'Critical' => [
            'label' => 'Critical',
            'color' => 'text-red-800',
            'bg' => 'bg-red-100',
            'dotColor' => 'bg-red-500',
            'icon' => 'error',
        ],
        'Closed' => [
            'label' => 'Closed',
            'color' => 'text-gray-700',
            'bg' => 'bg-gray-200',
            'dotColor' => 'bg-gray-500',
            'icon' => 'lock',
        ],
        'Completed' => [
            'label' => 'Completed',
            'color' => 'text-green-800',
            'bg' => 'bg-green-100',
            'dotColor' => 'bg-green-500',
            'icon' => 'check_circle',
        ],
        default => [
            'label' => 'Unknown',
            'color' => 'text-gray-600',
            'bg' => 'bg-gray-100',
            'dotColor' => 'bg-gray-400',
            'icon' => 'help',
        ],
    };
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center gap-2 px-3 py-1 ' . $badge['bg'] . ' ' . $badge['color'] . ' rounded-md text-[11px] font-bold']) }}>
    <span class="w-2 h-2 {{ $badge['dotColor'] }} rounded-full animate-pulse"></span>
    @if(!empty($badge['icon']))
        <span class="material-symbols-outlined text-[16px]">{{ $badge['icon'] }}</span>
    @endif
    <span>{{ $badge['label'] }}</span>
</div>
