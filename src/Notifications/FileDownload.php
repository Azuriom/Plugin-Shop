<?php

namespace Azuriom\Plugin\Shop\Notifications;

use Azuriom\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FileDownload extends Notification
{
    protected User $user;

    protected string $url;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user, string $url)
    {
        $this->user = $user;
        $this->url = $url;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->subject(trans('shop::mails.file.subject'))
            ->line(trans('shop::mails.file.intro', ['user' => $this->user->name]))
            ->action(trans('messages.actions.download'), $this->url);
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
}
