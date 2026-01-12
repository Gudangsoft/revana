<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewDeadlineReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;
    protected $daysRemaining;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $assignment, $daysRemaining = 0)
    {
        $this->assignment = $assignment;
        $this->daysRemaining = $daysRemaining;
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
                    ->subject('Pengingat Deadline Review - ' . config('app.name'))
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line('Ini adalah pengingat bahwa deadline review Anda akan segera berakhir.')
                    ->line('**Judul Artikel:** ' . $this->assignment->article_title)
                    ->line('**Nomor Artikel:** ' . $this->assignment->article_number)
                    ->line('**Deadline:** ' . ($this->assignment->deadline ? $this->assignment->deadline->format('d F Y') : '-'))
                    ->line('**Sisa Waktu:** ' . $this->daysRemaining . ' hari lagi')
                    ->action('Kerjakan Sekarang', $url)
                    ->line('Segera selesaikan review Anda sebelum deadline berakhir.')
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
            'days_remaining' => $this->daysRemaining,
        ];
    }
}
