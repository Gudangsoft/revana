<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewAssignmentNotification extends Notification
{
    use Queueable;

    protected $assignment;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $assignment)
    {
        $this->assignment = $assignment;
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
                    ->subject('Penugasan Review Artikel Baru - ' . config('app.name'))
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line('Anda mendapatkan penugasan review artikel baru.')
                    ->line('**Judul Artikel:** ' . $this->assignment->article_title)
                    ->line('**Nomor Artikel:** ' . $this->assignment->article_number)
                    ->line('**Deadline:** ' . ($this->assignment->deadline ? $this->assignment->deadline->format('d F Y') : '-'))
                    ->action('Lihat Detail Tugas', $url)
                    ->line('Silakan login ke sistem untuk melihat detail dan melakukan review.')
                    ->line('Terima kasih atas dedikasi Anda sebagai reviewer.')
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
            'deadline' => $this->assignment->deadline,
        ];
    }
}

