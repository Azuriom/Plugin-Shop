<?php

return [
    'title' => 'Shop',
    'welcome' => 'Welcome on the shop!',

    'buy' => 'Buy',

    'free' => 'Free',

    'fields' => [
        'balance' => 'Balance',
        'category' => 'Category',
        'code' => 'Code',
        'commands' => 'Commands',
        'currency' => 'Currency',
        'discount' => 'Discount',
        'expire_date' => 'Expire date',
        'gateways' => 'Gateways',
        'original_balance' => 'Original Balance',
        'package' => 'Package',
        'packages' => 'Packages',
        'payment_id' => 'Payment ID',
        'payments' => 'Payments',
        'price' => 'Price',
        'quantity' => 'Quantity',
        'required_packages' => 'Required Packages',
        'required_roles' => 'Required role',
        'role' => 'Role to set after purchase',
        'server' => 'Server',
        'start_date' => 'Start date',
        'status' => 'Status',
        'total' => 'Total',
        'user_id' => 'User ID',
        'user_limit' => 'User purchase limit',
    ],

    'actions' => [
        'duplicate' => 'Duplicate',
        'remove' => 'Remove',
    ],

    'goal' => [
        'title' => 'Goal of the month',
        'progress' => ':count% completed',
    ],

    'recent' => [
        'title' => 'Recent Payments',
        'empty' => 'No recent payments',
    ],

    'top' => [
        'title' => 'Top customer',
    ],

    'cart' => [
        'title' => 'Cart',
        'success' => 'Your purchase has been successfully completed.',
        'guest' => 'You must be logged in to make a purchase.',
        'empty' => 'Your cart is empty.',
        'checkout' => 'Checkout',
        'remove' => 'Remove',
        'clear' => 'Clear the cart',
        'pay' => 'Pay',
        'back' => 'Back to shop',
        'total' => 'Total: :total',
        'payable_total' => 'Total to pay: :total',
        'credit' => 'Credit',

        'confirm' => [
            'title' => 'Pay?',
            'price' => 'Are you sure you want to buy this cart for :price.',
        ],

        'errors' => [
            'money' => 'You don\'t have enough money to make this purchase.',
            'execute' => 'An unexpected error occurred during payment, your purchase got refund.',
        ],

        'terms' => 'I accept the :terms.',
        'terms_link' => 'terms of service',
    ],

    'coupons' => [
        'title' => 'Coupons',
        'add' => 'Add a coupon',
        'error' => 'This coupon does not exist, has expired or can no longer be used.',
        'cumulate' => 'You cannot use this coupon with an other coupon.',
    ],

    'payment' => [
        'title' => 'Payment',
        'manual' => 'Manual payment',

        'empty' => 'No payment methods currently available.',

        'info' => 'Purchase #:id on :website',
        'error' => 'The payment could not be completed.',

        'success' => 'Payment completed, you\'ll receive your purchase in-game in less than a minute.',

        'webhook' => 'New payment on the shop',
        'webhook_info' => 'Total: :total, ID: :id (:gateway)',
    ],

    'categories' => [
        'empty' => 'This category is empty.',
    ],

    'packages' => [
        'limit' => 'You have purchased the maximum possible for this packages.',
        'requirements' => 'You don\'t have the requirements to purchase this package.',
    ],

    'offers' => [
        'gateway' => 'Payment type',
        'amount' => 'Amount',

        'empty' => 'No offers are currently available.',
    ],

    'profile' => [
        'payments' => 'Your payments',
        'money' => 'Money: :balance',
    ],

    'giftcards' => [
        'title' => 'Giftcards',
        'success' => ':money have been credited to your account',
        'error' => 'This gift card does not exist, has expired or can no longer be used.',
        'add' => 'Add a gift card',
        'notification' => 'You received a giftcard, the code is :code (:balance).',
    ],
];
