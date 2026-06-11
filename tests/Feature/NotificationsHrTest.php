<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class NotificationsHrTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user      = User::factory()->create(['role' => 'hr']);
        $this->otherUser = User::factory()->create(['role' => 'hr']);
    }

    /** Helper: buat notifikasi dengan kolom wajib 'icon' */
    private function makeNotif(array $attrs): Notification
    {
        return Notification::create(array_merge([
            'icon'    => 'notifications',
            'is_read' => false,
        ], $attrs));
    }

    /** Halaman notifikasi memerlukan autentikasi */
    public function test_notifications_page_requires_auth(): void
    {
        $this->get(route('hr.notifications'))
            ->assertRedirect(route('login'));
    }

    /** Halaman notifikasi dapat diakses oleh HR yang login */
    public function test_notifications_page_is_accessible_by_hr(): void
    {
        $this->actingAs($this->user)
            ->get(route('hr.notifications'))
            ->assertSuccessful()
            ->assertSeeLivewire(\App\Livewire\Hr\NotificationsHr::class);
    }

    /** Filter 'all' menampilkan semua notifikasi milik user */
    public function test_filter_all_shows_all_user_notifications(): void
    {
        $this->makeNotif(['user_id' => $this->user->id, 'type' => 'application', 'title' => 'Notif 1', 'message' => 'Test 1']);
        $this->makeNotif(['user_id' => $this->user->id, 'type' => 'interview',   'title' => 'Notif 2', 'message' => 'Test 2', 'is_read' => true]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Hr\NotificationsHr::class)
            ->set('filter', 'all')
            ->assertSee('Notif 1')
            ->assertSee('Notif 2');
    }

    /** Filter 'unread' hanya menampilkan notifikasi yang belum dibaca */
    public function test_filter_unread_shows_only_unread(): void
    {
        $this->makeNotif(['user_id' => $this->user->id, 'type' => 'application', 'title' => 'Belum Dibaca', 'message' => 'Unread', 'is_read' => false]);
        $this->makeNotif(['user_id' => $this->user->id, 'type' => 'application', 'title' => 'Sudah Dibaca', 'message' => 'Read',   'is_read' => true]);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Hr\NotificationsHr::class)
            ->set('filter', 'unread')
            ->assertSee('Belum Dibaca')
            ->assertDontSee('Sudah Dibaca');
    }

    /** Filter 'interviews' hanya menampilkan notifikasi bertipe interview */
    public function test_filter_interviews_shows_only_interview_type(): void
    {
        $this->makeNotif(['user_id' => $this->user->id, 'type' => 'interview',   'title' => 'Jadwal Interview', 'message' => 'Interview msg']);
        $this->makeNotif(['user_id' => $this->user->id, 'type' => 'application', 'title' => 'Lamaran Masuk',   'message' => 'Application msg']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Hr\NotificationsHr::class)
            ->set('filter', 'interviews')
            ->assertSee('Jadwal Interview')
            ->assertDontSee('Lamaran Masuk');
    }

    /** markAsRead() menandai notifikasi milik user sebagai sudah dibaca */
    public function test_mark_as_read_updates_notification(): void
    {
        $notif = $this->makeNotif(['user_id' => $this->user->id, 'type' => 'application', 'title' => 'Tandai', 'message' => 'Msg']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Hr\NotificationsHr::class)
            ->call('markAsRead', $notif->id);

        $this->assertTrue($notif->fresh()->is_read);
    }

    /** markAsRead() tidak bisa dilakukan oleh user lain */
    public function test_mark_as_read_cannot_be_done_by_other_user(): void
    {
        $notif = $this->makeNotif(['user_id' => $this->user->id, 'type' => 'application', 'title' => 'Protected', 'message' => 'Msg']);

        Livewire::actingAs($this->otherUser)
            ->test(\App\Livewire\Hr\NotificationsHr::class)
            ->call('markAsRead', $notif->id);

        $this->assertFalse($notif->fresh()->is_read);
    }

    /** Notifikasi user lain tidak muncul */
    public function test_user_cannot_see_other_users_notifications(): void
    {
        $this->makeNotif(['user_id' => $this->otherUser->id, 'type' => 'application', 'title' => 'Notif Orang Lain', 'message' => 'Private']);

        Livewire::actingAs($this->user)
            ->test(\App\Livewire\Hr\NotificationsHr::class)
            ->assertDontSee('Notif Orang Lain');
    }
}
