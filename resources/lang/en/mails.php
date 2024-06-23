<?php

return [
    'payment' => [
        'subject' => 'Thank you for your purchase',
        'intro' => 'Thank you :user you for your purchase! Your payment has been confirmed and you will receive your purchases in a few minutes.',
        'total' => 'Total: :total',
        'transaction' => 'Transaction ID: :transaction (:gateway)',
        'date' => 'Date: :date',
        'subscription' => 'This payment is for a subscription started on :date, you can manage it in your profile.',
        'profile' => 'Go to profile',
    ],

    'giftcard' => [
        'subject' => 'Your giftcard code',
        'intro' => 'Thank you for your purchase! Your giftcard is now available.',
        'code' => 'Code: :code',
        'balance' => 'Balance: :balance',
    ],

    'file' => [
        'subject' => 'Your download link',
        'intro' => 'Thank you :user for your purchase! The download link for your purchase is available below.',
    ],
];
