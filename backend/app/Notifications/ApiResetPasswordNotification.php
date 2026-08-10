<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;
class ApiResetPasswordNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $token,
        public string $email,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
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
        return (new MailMessage)
            ->subject(Lang::get('passwords.email_subject'))
            ->line(Lang::get('passwords.email_intro'))
            ->line(Lang::get('passwords.email_token').' **'.$this->token.'**')
            ->line(Lang::get('passwords.email_expires', ['count' => config('auth.passwords.users.expire')]))
            ->line(Lang::get('passwords.email_ignore'));
    }
}
