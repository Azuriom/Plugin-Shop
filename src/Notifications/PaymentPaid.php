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
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(): MailMessage
    {
        $transactionId = $this->payment->isWithSiteMoney()
            ? '#'.$this->payment->id
            : $this->payment->transaction_id;

        return (new MailMessage())
            ->subject(trans('shop::mails.payment.subject'))
            ->line(trans('shop::mails.payment.intro', [
                'user' => $this->payment->user->name,
            ]))
            ->line(trans('shop::mails.payment.total', [
                'total' => $this->payment->formatPrice(),
            ]))
            ->line(trans('shop::mails.payment.transaction', [
                'transaction' => $transactionId,
                'gateway' => $this->payment->getTypeName(),
            ]))
            ->line(trans('shop::mails.payment.date', [
                'date' => format_date($this->payment->created_at, true),
            ]))
            ->when($this->payment->subscription, function (MailMessage $message) {
                $subscription = $this->payment->subscription;

                $message->line(trans('shop::mails.payment.subscription', [
                    'date' => format_date($subscription->created_at, true),
                ]))->action(trans('shop::mails.payment.subscription'), route('shop.profile'));
            });
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
