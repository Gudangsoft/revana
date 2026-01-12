<?php

namespace App\Notifications;

use App\Models\ReviewAssignment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReviewRevisionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $assignment;
    protected $feedback;

    /**
     * Create a new notification instance.
     */
    public function __construct(ReviewAssignment $assignment, $feedback = '')
    {
        $this->assignment = $assignment;
        $this->feedback = $feedback;
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
        
        $message = (new MailMessage)
                    ->subject('Review Perlu Revisi - ' . config('app.name'))
                    ->greeting('Halo ' . $notifiable->name . ',')
                    ->line('Review Anda memerlukan revisi dari admin.')
                    ->line('**Judul Artikel:** ' . $this->assignment->article_title)
                    ->line('**Nomor Artikel:** ' . $this->assignment->article_number);
        
        if ($this->feedback) {
            $message->line('**Feedback Admin:**')
                    ->line($this->feedback);
        }
        
        $message->action('Lihat Detail & Revisi', $url)
                ->line('Silakan perbaiki review Anda sesuai feedback yang diberikan.')
                ->salutation('Salam, ' . config('app.name'));
        
        return $message;
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
            'feedback' => $this->feedback,
        ];
    }
}
