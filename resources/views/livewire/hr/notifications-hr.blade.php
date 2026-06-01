<div>
    <!-- Flash Messages -->
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
                <div class="p-4 rounded-md border transition-all {{ $notification->is_read ? 'bg-surface-container-lowest border-surface-container-high' : 'bg-primary/5 border-primary/20' }}">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <span class="material-symbols-outlined text-primary text-[24px]">
                                {{ $notification->icon }}
                            </span>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h4 class="font-bold text-on-surface mb-1">{{ $notification->title }}</h4>
                            <p class="text-label-sm font-label-sm text-on-surface-variant mb-2">
                                {{ $notification->message }}
                            </p>
                            <p class="text-xs text-on-surface-variant/60">
                                {{ $notification->created_at->diffForHumans() }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex-shrink-0 flex items-center gap-2">
                            @if (!$notification->is_read)
                                <button wire:click="markAsRead({{ $notification->id }})"
                                        title="Tandai sebagai dibaca"
                                        class="p-2 text-on-surface-variant hover:bg-surface-container-high rounded-md transition-colors">
                                    <span class="material-symbols-outlined text-[18px]">done</span>
                                </button>
                            @endif
                            <button wire:click="delete({{ $notification->id }})"
                                    wire:confirm="Hapus notifikasi ini?"
                                    title="Hapus"
                                    class="p-2 text-error hover:bg-error/10 rounded-md transition-colors">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
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
