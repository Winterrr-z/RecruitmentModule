<div>
    <x-breadcrumb :items="[['label' => 'Notifications', 'url' => null]]" />
    <x-toast-alert />

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <span class="material-symbols-outlined text-primary text-[32px]">notifications</span>
            <h1 class="text-2xl font-bold text-on-surface">Notifikasi</h1>
        </div>
        <p class="text-on-surface-variant text-body-md">
            {{ $totalCount === 0 ? 'Anda tidak memiliki notifikasi' : 'Anda memiliki ' . $totalCount . ' notifikasi' }}
        </p>
    </div>

    <!-- Filter Tabs -->
    <div class="mb-6 flex gap-2 overflow-x-auto pb-2">
        <button wire:click="$set('filter', 'all')"
                class="{{ $filter === 'all' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface border border-surface-container-high' }} px-4 py-2 rounded-md font-label-sm text-sm transition-all">
            Semua ({{ $totalCount }})
        </button>
        <button wire:click="$set('filter', 'unread')"
                class="{{ $filter === 'unread' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface border border-surface-container-high' }} px-4 py-2 rounded-md font-label-sm text-sm transition-all">
            Belum Dibaca ({{ $unreadCount }})
        </button>
        <button wire:click="$set('filter', 'applications')"
                class="{{ $filter === 'applications' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface border border-surface-container-high' }} px-4 py-2 rounded-md font-label-sm text-sm transition-all">
            Aplikasi
        </button>
        <button wire:click="$set('filter', 'interviews')"
                class="{{ $filter === 'interviews' ? 'bg-primary text-white' : 'bg-surface-container-lowest text-on-surface border border-surface-container-high' }} px-4 py-2 rounded-md font-label-sm text-sm transition-all">
            Interview
        </button>
    </div>

    <!-- Notifications List -->
    @if ($notifications->isEmpty())
        <div class="flex flex-col items-center justify-center p-12 text-center bg-surface-container-lowest rounded-md border border-dashed border-outline-variant/50 shadow-sm">
            <span class="material-symbols-outlined text-[64px] text-on-surface-variant/30 mb-4">notifications_off</span>
            <h3 class="text-title-md font-title-md text-on-surface mb-2">Tidak Ada Notifikasi</h3>
            <p class="text-label-sm font-label-sm text-on-surface-variant">
                Anda tidak memiliki notifikasi untuk kategori ini.
            </p>
        </div>
    @else
        <div class="space-y-3">
            @foreach ($notifications as $notification)
                <div @if(!$notification->is_read) wire:click="markAsRead({{ $notification->id }})" @endif
                     class="p-4 rounded-md border transition-all {{ $notification->is_read ? 'bg-surface-container-lowest border-surface-container-high' : 'bg-primary/5 border-primary/20 cursor-pointer hover:bg-primary/10' }}">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0 mt-0.5">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center {{ $notification->is_read ? 'bg-surface-container-high text-on-surface-variant' : 'bg-primary/20 text-primary' }}">
                                <span class="material-symbols-outlined text-[20px]">
                                    {{ $notification->icon }}
                                </span>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold {{ $notification->is_read ? 'text-on-surface-variant' : 'text-on-surface' }} mb-1">{{ $notification->title }}</h4>
                            <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">
                                {{ $notification->message }}
                            </p>
                            <p class="text-[11px] text-on-surface-variant/60 font-semibold flex items-center gap-1">
                                <span class="material-symbols-outlined text-[14px]">schedule</span>
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $notifications->links() }}
        </div>
    @endif
</div>