<?php

namespace App\Livewire\Hr;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

/**
 * Class NotificationsHr
 *
 * Komponen Livewire untuk menampilkan halaman pusat notifikasi HR.
 * Mendukung filter (semua, belum dibaca, lamaran, jadwal wawancara)
 * dan paginasi daftar notifikasi.
 *
 * @package App\Livewire\Hr
 */
#[Layout('layouts.hr')]
class NotificationsHr extends Component
{
    use WithPagination;

    /** @var string Tipe filter yang sedang aktif ('all', 'unread', 'applications', 'interviews'). */
    public $filter = 'all';

    /**
     * Dijalankan otomatis ketika properti $filter berubah.
     * Mengatur ulang paginasi kembali ke halaman pertama.
     */
    public function updatedFilter()
    {
        $this->resetPage();
    }

    /**
     * Menandai satu notifikasi tertentu sebagai "Sudah Dibaca".
     *
     * @param int $notificationId ID notifikasi yang diklik.
     */
    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        // Pastikan notifikasi ditemukan dan benar-benar milik pengguna yang sedang masuk
        if ($notification && $notification->user_id == Auth::id()) {
            $notification->markAsRead();
            session()->flash('message', 'Notifikasi ditandai sudah dibaca');
        }
    }

    /**
     * Render komponen dengan layout HR dan kirimkan data notifikasi yang telah disaring (filter).
     */
    public function render()
    {
        // Kueri dasar: ambil semua notifikasi milik HR ini
        $query = Notification::where('user_id', Auth::id());

        // Terapkan saringan (filter) sesuai pilihan
        if ($this->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($this->filter === 'applications') {
            $query->where('type', 'like', '%application%');
        } elseif ($this->filter === 'interviews') {
            $query->where('type', 'interview');
        }

        // Ambil notifikasi secara menurun (terbaru di atas) dengan paginasi
        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        $unreadCount = Notification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->count();

        $totalCount = Notification::where('user_id', Auth::id())->count();

        return view('livewire.hr.notifications-hr', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
            'totalCount' => $totalCount,
        ]);
    }
}
