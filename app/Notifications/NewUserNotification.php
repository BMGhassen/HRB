<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class NewUserNotification extends Notification implements ShouldQueue
{
    use Queueable;
    protected $user;
    /**
     * Create a new notification instance.
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toDatabase($notifiable)
    {
        return [
            'user_id' => $this->user->id,
            'name' => $this->user->name,
            'email' => $this->user->email,
        ];
    }

    public function hasRole($role)
    {
        return $this->role === $role;
    }

    public function markAsRead()
    {
        $this->update(['read_at' => now()]);
    }
    /**
     * Get the array representation of the notification.
     *
     */
    public function toArray($notifiable)
    {
        return [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'user_id' => $this->user->id,
        ];
    }
}
