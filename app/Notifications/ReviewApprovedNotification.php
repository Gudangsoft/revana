<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;
    protected $points;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $assignment, $points = 0)
    {
        $this->assignment = $assignment;
        $this->points = $points;
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
        $url = route('reviewer.tasks.show', $this->assignment);
        
        return (new MailMessage)
                    ->subject('Review Anda Telah Disetujui - ' . config('app.name'))
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line('Selamat! Review Anda telah disetujui oleh admin.')
                    ->line('**Judul Artikel:** ' . $this->assignment->article_title)
                    ->line('**Nomor Artikel:** ' . $this->assignment->article_number)
                    ->line('**Poin yang Didapat:** ' . $this->points . ' poin')
                    ->action('Lihat Detail', $url)
                    ->line('Terima kasih atas kontribusi Anda dalam proses peer review.')
                    ->salutation('Salam, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'article_title' => $this->assignment->article_title,
            'points' => $this->points,
        ];
    }
}
