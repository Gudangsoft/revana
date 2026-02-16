<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SettingChangedNotification extends Notification
{
    use Queueable;

    protected string $action;
    protected string $adminName;
    protected array $changes;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $action, string $adminName, array $changes = [])
    {
        $this->action = $action;
        $this->adminName = $adminName;
        $this->changes = $changes;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[SIPERA] Pengaturan Sistem Diubah - ' . ucfirst($this->action))
            ->greeting('Halo ' . ($notifiable->name ?? 'Admin') . ',');

        switch ($this->action) {
            case 'maintenance_on':
                $mail->line('**Maintenance Mode telah DIAKTIFKAN** oleh ' . $this->adminName . '.')
                     ->line('Semua user kecuali Admin tidak bisa mengakses sistem.');
                break;
            case 'maintenance_off':
                $mail->line('**Maintenance Mode telah DINONAKTIFKAN** oleh ' . $this->adminName . '.')
                     ->line('Sistem kembali normal dan bisa diakses semua user.');
                break;
            case 'reset':
                $mail->line('**Semua pengaturan fitur telah di-reset ke default** oleh ' . $this->adminName . '.');
                break;
            case 'import':
                $mail->line('**Pengaturan fitur telah di-import** oleh ' . $this->adminName . '.')
                     ->line('Jumlah setting yang berubah: ' . count($this->changes));
                break;
            case 'role_change':
                $mail->line('**Role capability diubah** oleh ' . $this->adminName . '.');
                if (!empty($this->changes)) {
                    foreach ($this->changes as $key => $info) {
                        $mail->line('- `' . $key . '`: ' . ($info['old'] ?? '?') . ' → ' . ($info['new'] ?? '?'));
                    }
                }
                break;
            default:
                $mail->line('Pengaturan sistem telah diubah oleh ' . $this->adminName . '.');
                break;
        }

        $mail->action('Buka Feature Management', url('/admin/feature-management'))
             ->line('Waktu: ' . now()->format('d M Y H:i:s'))
             ->line('IP: ' . (request()->ip() ?? 'N/A'));

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'action'     => $this->action,
            'admin_name' => $this->adminName,
            'changes'    => $this->changes,
        ];
    }
}
