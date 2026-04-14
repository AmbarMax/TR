<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;

class UserResetPasswordNotification extends Notification
{
    use Queueable;

    protected $resetLink;
    protected $name;

    /**
     * Create a new notification instance.
     */
    public function __construct($resetLink, $name)
    {
        $this->resetLink = $resetLink;
        $this->name = $name;
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
        return (new MailMessage)
                    ->line(new HtmlString("<img class='logo' src=". Vite::apiMail('logo.png')." alt='Forgot password V2' />"))
                    ->line(new HtmlString('<div class="header_text">Reset Password</div>'))
                    ->line(new HtmlString("<div class='img-center'><img class='icon' src=". Vite::apiMail('lock.png')." alt='Forgot password V2' /></div>"))
                    ->line(new HtmlString("<div class='greeting_text'>Dear {$this->name},</div>"))
                    ->line(new HtmlString("<div class='content_text'>Your are receiving this email because we received a password reset request for your account</div>"))
                    ->action('Reset password', $this->resetLink)
                    ->line(new HtmlString("<div class='content_text light'>This password reset link will expire in 60 minutes. If you did not request a password reset, no further action is required</div>"));
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
