<?php

return [
    'title' => 'Shop',

    'buy' => 'Buy',

    'free' => 'Free',

    'fields' => [
        'price' => 'Price',
        'total' => 'Total',
        'quantity' => 'Quantity',
        'currency' => 'Currency',
        'category' => 'Category',
        'package' => 'Package',
        'packages' => 'Packages',
        'gateways' => 'Gateways',
        'servers' => 'Servers',
        'code' => 'Code',
        'discount' => 'Discount',
        'commands' => 'Commands',
        'start_date' => 'Start date',
        'expire_date' => 'Expire date',
        'required_packages' => 'Required Packages',
        'user_limit' => 'User purchase limit',
        'status' => 'Status',
        'payment_id' => 'Payment ID',
        'role' => 'Role to set after purchase',
        'user_id' => 'User ID',
        'required_roles' => 'Required role',
    ],

    'actions' => [
        'remove' => 'Remove',
        'duplicate' => 'Duplicate',
    ],

    'goal' => [
        'title' => 'Goal of the month',
        'progress' => ':count% completed',
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
        'credit' => 'Credit',

        'confirm' => [
            'title' => 'Pay?',
            'price' => 'Are you sure you want to buy this cart for :price.',
        ],

        'errors' => [
            'money' => 'You don\'t have enough money to make this purchase.',
            'execute' => 'An unexpected error occurred during payment, your purchase got refund.',
        ],
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

        'success' => 'Payment completed',
        'success_info' => 'You\'ll receive your purchase in-game in less than a minute.',

        'webhook' => 'New payment on the shop',
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
    ],

    'giftcards' => [
        'success' => ':money have been credited to your account',
        'error' => 'This gift card does not exist, has expired or can no longer be used.',
        'add' => 'Redeem a gift card',
    ],
];
