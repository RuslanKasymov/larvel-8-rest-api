<?php

namespace App\Notifications;

use App\Mails\ForgotPasswordMail;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ForgotPasswordNotification extends Notification
{
    use Queueable;

    protected $hash;

    public function __construct($hash)
    {
        $this->hash = $hash;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new ForgotPasswordMail($notifiable->email, [
            'hash' => $this->hash,
            'name' => $notifiable->name
        ]));
    }
}
