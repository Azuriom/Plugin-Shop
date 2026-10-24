<?php

namespace Azuriom\Plugin\Shop\Notifications;

use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CodesDelivered extends Notification
{
    protected Package $package;

    /**
     * @var array<int, string>
     */
    protected array $codes;

    /**
     * Create a new notification instance.
     */
    public function __construct(Package $package, array $codes)
    {
        $this->package = $package;
        $this->codes = $codes;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        // TODO use Markdown::escape function from Azuriom
        $codes = array_map(fn (string $code) => '- '.addcslashes($code, '\\`*_[]()'), $this->codes);

        return (new MailMessage())
            ->subject(trans('shop::mails.codes.subject'))
            ->line(trans('shop::mails.codes.intro', [
                'package' => addcslashes($this->package->name, '\\`*_[]()'),
            ]))
            ->line([implode("\n", $codes)]) // Use array to preserve line breaks
            ->action(trans('shop::mails.payment.profile'), route('shop.profile'));
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
