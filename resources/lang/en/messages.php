<?php

return [
    'title' => 'Shop',

    'buy' => 'Buy',

    'free' => 'Free',

    'month-goal' => 'Goal of the month',
    'month-goal-current' => ':count% completed',

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
        'payment-id' => 'Payment ID',
        'role' => 'Role to set after purchase',
        'user_id' => 'User ID',
        'required_roles' => 'Required role',
    ],

    'actions' => [
        'remove' => 'Remove',
        'duplicate' => 'Duplicate',
    ],

    'cart' => [
        'title' => 'Cart',
        'error-money' => 'You don\'t have enough money to make this purchase.',
        'error-execute' => 'An unexpected error occurred during payment, your purchase got refund.',
        'purchase' => 'Your purchase has been successfully completed.',

        'pay-confirm-title' => 'Pay?',
        'pay-confirm' => 'Are you sure you want to buy this cart for :price.',

        'guest' => 'You must be logged in to make a purchase.',
        'empty' => 'Your cart is empty.',

        'checkout' => 'Checkout',
        'remove' => 'Remove',
        'clear' => 'Clear the cart',
        'pay' => 'Pay',

        'coupons' => 'Coupons',
        'add-coupon' => 'Add a coupon',
        'invalid-coupon' => 'This coupon does not exist, has expired or can no longer be used.',
        'cannot-cumulate' => 'You cannot use this coupon with an other coupon.',

        'back' => 'Back to shop',

        'total' => 'Total: :total',

        'credit' => 'Credit',
    ],

    'payment' => [
        'title' => 'Payment',
        'manual' => 'Manual payment',

        'empty' => 'No payment methods currently available.',

        'info' => 'Purchase #:id on :website',
        'error' => 'The payment could not be completed.',

        'success' => 'Payment completed',
        'success-info' => 'You\'ll receive your purchase in-game in less than a minute.',

        'redirect-info' => 'If you are not redirected automatically check that javascript is enabled on your browser.',

        'webhook' => 'New payment on the shop',
    ],

    'packages' => [
        'limit' => 'You have purchased the maximum possible for this packages.',
        'requirements' => 'You don\'t have the requirements to purchase this package.',
    ],

    'offers' => [
        'title-payment' => 'Payment type',
        'title-select' => 'Amount',

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
