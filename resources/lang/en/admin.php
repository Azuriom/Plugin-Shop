<?php

return [
    'nav' => [
        'title' => 'Shop',

        'settings' => 'Settings',
        'packages' => 'Packages',
        'gateways' => 'Gateways',
        'offers' => 'Offers',
        'coupons' => 'Coupons',
        'discounts' => 'Discounts',
        'payments' => 'Payments',
        'purchases' => 'Purchases',
    ],

    'permissions' => [
        'admin' => 'View and manage shop plugin',
    ],

    'categories' => [
        'title' => 'Categories',
        'title-edit' => 'Edit category :category',
        'title-create' => 'Create category',

        'status' => [
            'created' => 'The category has been created.',
            'updated' => 'This category has been modified.',
            'deleted' => 'This category has been deleted.',

            'delete-items' => 'A category with packages can\'t be deleted.',
        ],

        'enable' => 'Enable the category',
    ],

    'offers' => [
        'title' => 'Offers',
        'title-edit' => 'Edit offer :offer',
        'title-create' => 'Create offer',

        'status' => [
            'created' => 'The offer has been created.',
            'updated' => 'This offer has been modified.',
            'deleted' => 'This offer has been deleted.',
        ],

        'enable' => 'Enable the offer',
    ],

    'coupons' => [
        'title' => 'Coupons',
        'title-edit' => 'Edit coupon :coupon',
        'title-create' => 'Create coupon',

        'status' => [
            'created' => 'The coupon has been created.',
            'updated' => 'This coupon has been modified.',
            'deleted' => 'This coupon has been deleted.',
        ],

        'global' => 'Should the coupon be active on all the shop ?',

        'active' => 'Active',
        'enable' => 'Enable the coupon',
    ],

    'discounts' => [
        'title' => 'Discounts',
        'title-edit' => 'Edit discount :discount',
        'title-create' => 'Create discount',

        'status' => [
            'created' => 'The discount has been created.',
            'updated' => 'This discount has been modified.',
            'deleted' => 'This discount has been deleted.',
        ],

        'global' => 'Should the discount be active on all the shop ?',

        'active' => 'Active',
        'enable' => 'Enable the discount',
    ],

    'packages' => [
        'title' => 'Packages',
        'title-edit' => 'Edit package :package',
        'title-create' => 'Create package',

        'status' => [
            'created' => 'The package has been created.',
            'updated' => 'This package has been modified.',
            'deleted' => 'This package has been deleted.',

            'order-updated' => 'The packages have been updated.',
        ],

        'commands-info' => 'You can use <code>{player}</code> to use the player name.',

        'need-online' => 'The user must be online to receive the package (only available with AzLink)',
        'enable-quantity' => 'Enable the quantity',

        'create-category' => 'Create category',
        'create-package' => 'Create package',

        'enable' => 'Enable this package',
    ],

    'gateways' => [
        'title' => 'Gateways',
        'title-edit' => 'Edit gateway :gateway',
        'title-create' => 'Add gateway',

        'subtitle-current' => 'Current gateways',
        'subtitle-add' => 'Add a new gateway',

        'status' => [
            'created' => 'The gateway has been created.',
            'updated' => 'This gateway has been modified.',
            'deleted' => 'This gateway has been deleted.',
        ],

        'api-key' => 'API key',
        'public-key' => 'Public key',
        'private-key' => 'Private key',
        'secret-key' => 'Secret key',
        'service-id' => 'Service ID',
        'client-id' => 'Client ID',
        'env' => 'Environment',

        'paypal-email' => 'PayPal Email Address',
        'paysafecard-info' => 'In order to be able to accept payments by paysafecard, you must be a <a href="https://www.paysafecard.com/en/business/" target="_blank" rel="noopener noreferrer">paysafecard partner</a>. Other methods exist but only this one is allowed by paysafecard.',
        'stripe-info' => 'On the Stripe dashboard you need to set the webhook URL to <code>:url</code>.',
        'paymentwall-info' => 'On the PaymentWall dashboard you need to set the pingback URL to <code>:url</code>.',

        'enable' => 'Enable the gateway',
    ],

    'payments' => [
        'title' => 'Payments',

        'fields' => [
            'status' => 'Status',
            'payment-id' => 'Payment ID',
        ],

        'card' => 'Shop payments',

        'payment-status' => [
            'created' => 'Created',
            'cancelled' => 'Cancelled',
            'pending' => 'Pending',
            'expired' => 'Expired',
            'success' => 'Success',
            'delivered' => 'Delivered',
            'error' => 'Error',
        ],
    ],

    'purchases' => [
        'title' => 'Purchases',
    ],

    'settings' => [
        'title' => 'Shop settings',
        'use-site-money' => 'Activate purchases with the site currency.',
    ],
];
