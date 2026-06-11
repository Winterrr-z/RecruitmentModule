<?php

namespace App\Livewire\Hr;

use App\Models\Notification;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

#[Layout('layouts.hr')]
class NotificationsHr extends Component
{
    use WithPagination;

    public $filter = 'all';

    public function updatedFilter()
    {
        $this->resetPage();
    }

    public function markAsRead($notificationId)
    {
        $notification = Notification::find($notificationId);
        if ($notification && $notification->user_id === Auth::id()) {
            $notification->markAsRead();
            session()->flash('message', 'Notifikasi ditandai sudah dibaca');
        }
    }

    public function render()
    {
        $query = Notification::where('user_id', Auth::id());

        if ($this->filter === 'unread') {
            $query->where('is_read', false);
        } elseif ($this->filter === 'applications') {
            $query->where('type', 'like', '%application%');
        } elseif ($this->filter === 'interviews') {
            $query->where('type', 'interview');
        }

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
