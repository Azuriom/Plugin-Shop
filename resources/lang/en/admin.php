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
        'subscriptions' => 'Subscriptions',
        'variables' => 'Variables',
        'purchases' => 'Purchases',
        'statistics' => 'Statistics',
    ],

    'permissions' => [
        'settings' => 'Manage shop settings',
        'packages' => 'Manage shop packages and categories',
        'gateways' => 'Manage shop payment gateways',
        'promotions' => 'Manage shop coupons and discounts',
        'giftcards' => 'Manage shop gift cards',
        'payments' => 'Create and view shop payments/purchases and subscriptions',
    ],

    'categories' => [
        'title' => 'Categories',
        'edit' => 'Edit category :category',
        'create' => 'Create category',

        'parent' => 'Parent category',
        'delete_error' => 'A category with packages can\'t be deleted.',

        'cumulate' => 'Cumulate purchases in this category',
        'cumulate_info' => 'Users who have already purchased a package in this category will get a discount and will only pay the difference when purchasing a more expensive package.',
        'single_purchase' => 'Limit the user to purchasing a single package from this category',
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

        'global' => 'Should the coupon be active on all the shop?',
        'cumulate' => 'Allow to use this coupon with other coupons',
        'user_limit' => 'User limit',
        'global_limit' => 'Global limit',
        'active' => 'Active',
        'usage' => 'Under usage limit',
        'enable' => 'Enable the coupon',
    ],

    'giftcards' => [
        'title' => 'Gift Cards',
        'edit' => 'Edit the gift card :giftcard',
        'create' => 'Create a gift card',

        'global_limit' => 'Global limit',
        'active' => 'Active',
        'enable' => 'Enable the gift card',
        'link' => 'Shareable link',
        'pending' => 'A payment is pending, the balance might not be updated yet.',
    ],

    'discounts' => [
        'title' => 'Discounts',
        'edit' => 'Edit discount :discount',
        'create' => 'Create discount',

        'global' => 'Should the discount be active on all the shop?',
        'active' => 'Active',
        'enable' => 'Enable the discount',
        'restricted' => 'Restrict this discount to certain roles only',
    ],

    'packages' => [
        'title' => 'Packages',
        'edit' => 'Edit package :package',
        'create' => 'Create package',

        'every' => 'every',
        'after' => 'after',

        'updated' => 'The packages have been updated.',

        'variables' => 'Custom variables',
        'files' => 'Downloadable files',
        'role' => 'Role to set when the package expires',
        'money' => 'Money to give to the user after purchase',
        'has_giftcard' => 'Create a giftcard after the purchase and give it to the user',
        'giftcard_balance' => 'Giftcard balance',
        'giftcard_fixed' => 'Fixed amount',
        'giftcard_dynamic' => 'Price of the package',
        'command' => 'The command must not start with <code>/</code>. You can use <code>{player}</code> for the player name. For Steam games, you can also use <code>{steam_id}</code> and <code>{steam_id_32}</code>. The others available placeholders are: :placeholders.',
        'has_user_limit' => 'Add an individual purchase limit per user for this package',
        'has_global_limit' => 'Add a global purchase limit for this package',
        'limit_period' => 'Limit period',
        'limits_no_expired' => 'Exclude expired packages when calculating purchase limits',
        'no_period' => 'No period',
        'custom_price' => 'Allow the user to choose the price to pay (the package price will then be the minimum)',
        'enable_quantity' => 'Enable the quantity',

        'billing' => 'Billing type',
        'billing_period' => 'Billing period',
        'one_off' => 'One-off payment',
        'subscription' => 'Subscription with automatic renewal',
        'subscription_info' => 'Make sure your payment gateway supports subscriptions. When a subscription is created, the user will be charged the same price until the subscription is canceled (any discount active during the first payment will be applied to the next payments).',
        'expiring' => 'One-off payment with expiration',
        'expiring_info' => 'The product will be automatically removed after the specified period.',
        'scheduler' => 'CRON tasks are not enabled, you must set them up to use subscriptions and expiring packages, see the <a href="https://azuriom.com/docs/faq" target="_blank" rel="noopener norefferer">FAQ</a> for more information.',

        'create_category' => 'Create category',
        'create_package' => 'Create package',

        'enable' => 'Enable this package',
    ],

    'commands' => [
        'online' => 'Wait for the player to be online before executing the command (requires AzLink)',
        'offline' => 'Execute the command directly, whether the player is online or not',
        'servers' => 'You need to add at least one server to create a command.',

        'trigger' => 'Command trigger',
        'command' => 'Command',
        'condition' => 'Condition to execute the command',

        'triggers' => [
            'purchase' => 'Initial Purchase',
            'expiration' => 'After Expiration',
            'refund' => 'Payment Refunded',
            'chargeback' => 'Chargeback',
            'renewal' => 'Subscription Renewal',
        ],
    ],

    'gateways' => [
        'title' => 'Gateways',
        'edit' => 'Edit gateway :gateway',
        'create' => 'Add gateway',

        'current' => 'Current gateways',
        'add' => 'Add a new gateway',
        'info' => 'If you are having problems with payments when using Cloudflare or a firewall, try following the steps in the <a href="https://azuriom.com/docs/faq" target="_blank" rel="noopener norefferer">FAQ</a>.',

        'country' => 'Country',
        'sandbox' => 'Sandbox',
        'api-key' => 'API key',
        'public-key' => 'Public key',
        'private-key' => 'Private key',
        'secret-key' => 'Secret key',
        'endpoint-secret' => 'Signing Secret',
        'service-id' => 'Service ID',
        'client-id' => 'Client ID',
        'merchant-id' => 'Merchant ID',
        'project-id' => 'Project ID',
        'webhook_id' => 'Webhook ID',
        'website_id' => 'Website ID',
        'env' => 'Environment',

        'subscription' => 'This payment gateway supports automatic subscription renewal.',
        'no_subscription' => 'This payment gateway does not support automatic subscription renewal.',

        'paypal_email' => 'PayPal Email Address',
        'paypal_info' => 'Please make sure to input the <strong>primary</strong> email address of the PayPal account.',
        'paypal_checkout' => 'On the PayPal dashboard, in your API application, create a webhook with URL <code>:url</code> and select the events <code>:events</code>.',
        'stripe_info' => 'On the Stripe dashboard you need to set the webhook URL to <code>:url</code> and select the events: <code>:events</code>.',
        'paymentwall_info' => 'On the PaymentWall dashboard you need to set the pingback URL to <code>:url</code>.',
        'xsolla' => 'On the Xsolla dashboard you need to set the webhook URL to <code>:url</code>, enable \'Transaction external ID\' in the \'Pay station\' settings, test the webhooks and then enable \'Checkout\' in the \'Pay Station\' settings.',
        'skrill_email' => 'Skrill Email Address',
        'skrill_secret' => 'Secret Word',
        'skrill_info' => 'The website ID and the Secret Word can be found in the Skrill dashboard under "Developer Settings".',

        'enable' => 'Enable the gateway',
    ],

    'payments' => [
        'title' => 'Payments',
        'show' => 'Payment #:payment',

        'info' => 'Payment information',
        'items' => 'Purchased items',

        'card' => 'Shop payments',

        'status' => [
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

    'subscriptions' => [
        'title' => 'Subscriptions',
        'show' => 'Subscription #:subscription',

        'info' => 'Subscription information',
        'error' => 'To continue, you must cancel associated subscriptions and wait for their expiration.',
        'setup' => 'Subscription package can be set up in a package settings, just under the price field.',

        'status' => [
            'active' => 'Active',
            'canceled' => 'Canceled',
            'expired' => 'Expired',
        ],
    ],

    'settings' => [
        'title' => 'Shop settings',
        'enable_home' => 'Enable the shop’s home page',
        'home_message' => 'Home message',
        'use_site_money' => 'Enable purchases with the site currency.',
        'use_site_money_info' => 'When enabled, the packages in the shop can only be purchased with website money. In order for users to credit their account, you can set up offers in the shop.',
        'webhook' => 'Discord Webhook URL',
        'webhook_info' => 'When an user make a payment (except for purchases with website money!), it will create a notification on this webhook. Leave empty to disable.',
        'commands' => 'Global commands',
        'recent_payments' => 'Recent payments limit to display in sidebar (set to 0 to disable)',
        'display_amount' => 'Display amount spend in recent payments and top customer',
        'top_customer' => 'Display top monthly customer in sidebar',

        'terms_required' => 'Require users to accept terms of service before purchase',
        'terms' => 'Terms of service',
        'terms_info' => 'Links in Markdown format, for example: <code>I accept the [terms of service](https://example.com/terms) of the shop.</code>',
    ],

    'variables' => [
        'title' => 'Variables',
        'edit' => 'Edit variable :variable',
        'create' => 'Create variable',

        'info' => 'Variables are custom fields that can be added to packages. They can be used to ask for additional information from the user when purchasing a package.',

        'name' => 'The internal name of the variable, cannot be changed after creation.',
        'required' => 'Should this variable be required?',
        'options' => 'Options',

        'text' => 'Text',
        'number' => 'Number',
        'email' => 'E-Mail',
        'checkbox' => 'Checkbox',
        'dropdown' => 'Dropdown',
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

        'shop-giftcards' => [
            'used' => 'Used giftcard #:id (:amount)',
        ],

        'settings' => 'Updated shop settings',
    ],

    'statistics' => [
        'title' => 'Statistics',
        'total' => 'Total',
        'recent' => 'Recent payments',
        'count' => 'Payments count',
        'estimated' => 'Estimated earnings',
        'month' => 'Payments during this month',
        'month_estimated' => 'Estimated earnings this month',
    ],

];
