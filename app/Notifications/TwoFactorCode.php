<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorCode extends Notification
{
    use Queueable;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->subject('Security Protocol: Verification Code')
                    ->greeting('Hello, ' . $notifiable->name)
                    ->line('A login attempt requires an additional layer of security.')
                    ->line('Your verification code is:')
                    ->line('**' . $this->code . '**')
                    ->line('This code will expire in 10 minutes.')
                    ->line('If you did not attempt to login, please secure your account immediately.')
                    ->salutation('Strategic Inventory Nexus');
    }

    public function toArray($notifiable)
    {
        return [
            'code' => $this->code
        ];
    }
}
