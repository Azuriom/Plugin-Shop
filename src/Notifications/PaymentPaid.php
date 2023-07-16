<?php

namespace Azuriom\Plugin\Shop\Notifications;

use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentPaid extends Notification
{
    protected Payment $payment;

    /**
     * Create a new notification instance.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Payment  $payment
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
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
        $currency = $this->payment->isWithSiteMoney()
            ? money_name($this->payment->price)
            : currency_display($this->payment->currency);
        $transactionId = $this->payment->isWithSiteMoney()
            ? '#'.$this->payment->id
            : $this->payment->transaction_id;
        $gateway = $this->payment->isWithSiteMoney()
            ? site_name()
            : $this->payment->getTypeName();

        return (new MailMessage())
            ->subject(trans('shop::mails.payment.subject'))
            ->line(trans('shop::mails.payment.intro', [
                'user' => $this->payment->user->name,
            ]))
            ->line(trans('shop::mails.payment.total', [
                'total' => $this->payment->price.' '.$currency,
            ]))
            ->line(trans('shop::mails.payment.transaction', [
                'transaction' => $transactionId,
                'gateway' => $gateway,
            ]))
            ->line(trans('shop::mails.payment.date', [
                'date' => format_date($this->payment->created_at, true),
            ]));
    }
}
