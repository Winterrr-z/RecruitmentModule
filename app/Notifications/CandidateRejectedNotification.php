<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CandidateRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $lowongan;

    /**
     * Create a new notification instance.
     */
    public function __construct($lowongan)
    {
        $this->lowongan = $lowongan;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $jabatan = $this->lowongan ? $this->lowongan->jabatan : 'Posisi Pekerjaan';
        
        return (new MailMessage)
                    ->subject('Update Status Lamaran - ' . $jabatan)
                    ->greeting('Halo ' . $notifiable->nama . ',')
                    ->line('Terima kasih atas minat Anda untuk bergabung bersama kami pada posisi ' . $jabatan . '.')
                    ->line('Setelah melakukan pertimbangan secara menyeluruh, dengan berat hati kami menginformasikan bahwa kami belum dapat melanjutkan proses lamaran Anda ke tahap berikutnya pada saat ini karena kuota untuk posisi tersebut telah terpenuhi.')
                    ->line('Kami sangat menghargai waktu dan usaha yang Anda luangkan dalam proses seleksi ini.')
                    ->line('Kami akan menyimpan profil Anda dalam database kami dan mungkin akan menghubungi Anda di masa mendatang jika ada peluang yang sesuai dengan kualifikasi Anda.')
                    ->salutation('Salam hormat, Tim Rekrutmen');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
