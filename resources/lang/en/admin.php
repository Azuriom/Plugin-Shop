<?php

return [
    'nav' => [
        'title' => 'Shop',

        'settings' => 'Settings',
        'packages' => 'Packages',
        'gateways' => 'Gateways',
        'offers' => 'Offers',
        'coupons' => 'Coupons',
        'giftcards' => 'Gift Cards',
        'discounts' => 'Discounts',
        'payments' => 'Payments',
        'purchases' => 'Purchases',
        'statistics' => 'Statistics',
    ],

    'permissions' => [
        'admin' => 'Manage shop plugin',
    ],

    'categories' => [
        'title' => 'Categories',
        'edit' => 'Edit category :category',
        'create' => 'Create category',

        'delete_error' => 'A category with packages can\'t be deleted.',

        'cumulate_purchases' => 'Cumulate purchases in this category (users will only pay the difference when purchasing a more expensive package)',
        'enable' => 'Enable the category',
    ],

    'offers' => [
        'title' => 'Offers',
        'edit' => 'Edit offer :offer',
        'create' => 'Create offer',

        'enable' => 'Enable this offer',
    ],

    'coupons' => [
        'title' => 'Coupons',
        'edit' => 'Edit coupon :coupon',
        'create' => 'Create coupon',

        'global' => 'Should the coupon be active on all the shop ?',
        'cumulate' => 'Allow to use this coupon with other coupons',
        'user_limit' => 'User limit',
        'global_limit' => 'Global limit',
        'active' => 'Active',
        'enable' => 'Enable the coupon',
    ],

    'giftcards' => [
        'title' => 'Gift Cards',
        'edit' => 'Edit the gift card :giftcard',
        'create' => 'Create a gift card',

        'global_limit' => 'Global limit',
        'active' => 'Active',
        'enable' => 'Enable the gift card',
    ],

    'discounts' => [
        'title' => 'Discounts',
        'edit' => 'Edit discount :discount',
        'create' => 'Create discount',

        'global' => 'Should the discount be active on all the shop ?',
        'active' => 'Active',
        'enable' => 'Enable the discount',
    ],

    'packages' => [
        'title' => 'Packages',
        'edit' => 'Edit package :package',
        'create' => 'Create package',

        'updated' => 'The packages have been updated.',

        'command' => 'The command must not start with <code>/</code>. You can use <code>{player}</code> to use the player name. For Steam games, you can also use <code>{steam_id}</code> and <code>{steam_id_32}</code>.',

        'need-online' => 'Execute commands when the user is online on the server (only available with AzLink)',
        'enable-quantity' => 'Enable the quantity',

        'create-category' => 'Create category',
        'create-package' => 'Create package',

        'enable' => 'Enable this package',
    ],

    'gateways' => [
        'title' => 'Gateways',
        'edit' => 'Edit gateway :gateway',
        'create' => 'Add gateway',

        'subtitle-current' => 'Current gateways',
        'subtitle-add' => 'Add a new gateway',

        'country' => 'Country',
        'sandbox' => 'Sandbox',
        'api-key' => 'API key',
        'public-key' => 'Public key',
        'private-key' => 'Private key',
        'secret-key' => 'Secret key',
        'service-id' => 'Service ID',
        'client-id' => 'Client ID',
        'merchant-id' => 'Merchant ID',
        'project-id' => 'Project ID',
        'env' => 'Environment',
        'methods' => 'Payment methods',
        'methods-info' => 'Credit cards are enabled by default. You can find more information in the <a href=":docs">Stripe documentation</a>.',

        'paypal-email' => 'PayPal Email Address',
        'paysafecard-info' => 'In order to be able to accept payments by paysafecard, you must be a <a href="https://www.paysafecard.com/en/business/" target="_blank" rel="noopener noreferrer">paysafecard partner</a>. Other methods exist but only this one is allowed by paysafecard.',
        'stripe-info' => 'On the Stripe dashboard you need to set the webhook URL to <code>:url</code> and select the event <code>checkout.session.completed</code>.',
        'paymentwall-info' => 'On the PaymentWall dashboard you need to set the pingback URL to <code>:url</code>.',
        'xsolla' => 'On the Xsolla dashboard you need to set the webhook URL to <code>:url</code>, enable \'Transaction external ID\' in the \'Pay station\' settings, test the webhooks and then enable \'Checkout\' in the \'Pay Station\' settings.',

        'enable' => 'Enable the gateway',
    ],

    'payments' => [
        'title' => 'Payments',
        'title-show' => 'Payment #:payment',

        'info' => 'Payment information',
        'items' => 'Purchased items',

        'card' => 'Shop payments',

        'payment-status' => [
            'pending' => 'Pending',
            'expired' => 'Expired',
            'chargeback' => 'Chargeback',
            'completed' => 'Completed',
            'refunded' => 'Refunded',
            'error' => 'Error',
        ],
    ],

    'purchases' => [
        'title' => 'Purchases',
    ],

    'settings' => [
        'title' => 'Shop settings',
        'home_message' => 'Home message',
        'use-site-money' => 'Enable purchases with the site currency.',
        'webhook' => 'Discord Webhook URL',
        'webhook-info' => 'When an user make a payment it will create a notification on this webhook. Leave empty to disable',
        'commands' => 'Global commands',
    ],

    'logs' => [
        'shop-gateways' => [
            'created' => 'Created gateway #:id',
            'updated' => 'Updated gateway #:id',
            'deleted' => 'Deleted gateway #:id',
        ],

        'shop-packages' => [
            'created' => 'Created package #:id',
            'updated' => 'Updated package #:id',
            'deleted' => 'Deleted package #:id',
        ],

        'shop-offers' => [
            'created' => 'Created offer #:id',
            'updated' => 'Updated offer #:id',
            'deleted' => 'Deleted offer #:id',
        ],
    ],

    'statistics' => [
        'title' => 'Statistics',
        'total' => 'Total',
        'recent-payments' => 'Recent payments',
        'count' => 'Payments count',
        'estimated' => 'Estimated earnings',
        'month' => 'Payments during this month',
        'month-estimated' => 'Estimated earnings this month',
    ],

];
