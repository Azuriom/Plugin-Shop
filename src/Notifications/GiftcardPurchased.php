<?php

namespace Azuriom\Plugin\Shop\Notifications;

use Azuriom\Plugin\Shop\Models\Giftcard;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class GiftcardPurchased extends Notification
{
    protected Giftcard $giftcard;

    /**
     * Create a new notification instance.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Payment  $payment
     */
    public function __construct(Giftcard $giftcard)
    {
        $this->giftcard = $giftcard;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage())
            ->subject(trans('shop::mails.giftcard.subject'))
            ->line(trans('shop::mails.giftcard.intro'))
            ->line(trans('shop::mails.giftcard.code', [
                'code' => $this->giftcard->code,
            ]))
            ->line(trans('shop::mails.giftcard.balance', [
                'balance' => shop_format_amount($this->giftcard->balance),
            ]))
            ->line(trans('shop::mails.payment.date', [
                'date' => format_date($this->giftcard->created_at, true),
            ]));
    }
}
