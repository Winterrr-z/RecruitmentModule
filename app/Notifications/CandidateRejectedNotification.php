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
        $job_title = $this->lowongan ? $this->lowongan->job_title : 'Posisi Pekerjaan';
        
        return (new MailMessage)
                    ->subject('Update Status Lamaran - ' . $job_title)
                    ->view('emails.rejected-letter', [
                        'name' => $notifiable->name,
                        'jobTitle' => $job_title,
                    ]);
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
