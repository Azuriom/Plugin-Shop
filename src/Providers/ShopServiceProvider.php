<?php

namespace Azuriom\Plugin\Shop\Providers;

use Azuriom\Extensions\Plugin\AdminUserEditComposer;
use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Commands\PaymentExpireCommand;
use Azuriom\Plugin\Shop\Commands\SubscriptionRenewCommand;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Azuriom\Plugin\Shop\Models\Offer;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Observers\UserObserver;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Azuriom\Plugin\Shop\View\Composers\ShopAdminDashboardComposer;
use Azuriom\Plugin\Shop\View\Composers\ShopAdminUserComposer;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;

class ShopServiceProvider extends BasePluginServiceProvider
{
    /**
     * Register any plugin services.
     */
    public function register(): void
    {
        require_once __DIR__.'/../../vendor/autoload.php';

        $this->app->singleton(PaymentManager::class);

        Relation::morphMap([
            'shop.offers' => Offer::class,
            'shop.packages' => Package::class,
        ]);
    }

    /**
     * Bootstrap any plugin services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerUserNavigation();

        $this->registerAdminNavigation();

        $this->registerSchedule();

        $this->commands([
            PaymentExpireCommand::class,
            SubscriptionRenewCommand::class,
        ]);

        View::composer('admin.dashboard', ShopAdminDashboardComposer::class);

        if (class_exists(AdminUserEditComposer::class)) {
            View::composer('admin.users.edit', ShopAdminUserComposer::class);
        }

        Permission::registerPermissions([
            'shop.settings' => 'shop::admin.permissions.settings',
            'shop.packages' => 'shop::admin.permissions.packages',
            'shop.gateways' => 'shop::admin.permissions.gateways',
            'shop.promotions' => 'shop::admin.permissions.promotions',
            'shop.giftcards' => 'shop::admin.permissions.giftcards',
            'shop.payments' => 'shop::admin.permissions.payments',
        ]);

        ActionLog::registerLogModels([
            Offer::class,
            Package::class,
            Gateway::class,
        ], 'shop::admin.logs');

        ActionLog::registerLogs([
            'shop-giftcards.used' => [
                'icon' => 'credit-card',
                'color' => 'info',
                'message' => 'shop::admin.logs.shop-giftcards.used',
                'model' => Giftcard::class,
            ],
            'shop.settings.updated' => [
                'icon' => 'cart',
                'color' => 'info',
                'message' => 'shop::admin.logs.settings',
            ],
        ]);

        User::observe(UserObserver::class);
    }

    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('shop:subscriptions')->hourly();
        $schedule->command('shop:payments')->hourly();
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array<string, string>
     */
    protected function routeDescriptions(): array
    {
        return [
            'shop.home' => trans('shop::messages.title'),
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array<string, array<string, string>>
     */
    protected function adminNavigation(): array
    {
        return [
            'shop' => [
                'name' => trans('shop::admin.nav.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-cart',
                'route' => 'shop.admin.*',
                'items' => [
                    'shop.admin.settings' => [
                        'name' => trans('shop::admin.nav.settings'),
                        'permission' => 'shop.settings',
                    ],
                    'shop.admin.packages.index' => [
                        'name' => trans('shop::admin.nav.packages'),
                        'permission' => 'shop.packages',
                    ],
                    'shop.admin.gateways.index' => [
                        'name' => trans('shop::admin.nav.gateways'),
                        'permission' => 'shop.gateways',
                    ],
                    'shop.admin.offers.index' => [
                        'name' => trans('shop::admin.nav.offers'),
                        'permission' => 'shop.gateways',
                    ],
                    'shop.admin.discounts.index' => [
                        'name' => trans('shop::admin.nav.discounts'),
                        'permission' => 'shop.promotions',
                    ],
                    'shop.admin.coupons.index' => [
                        'name' => trans('shop::admin.nav.coupons'),
                        'permission' => 'shop.promotions',
                    ],
                    'shop.admin.giftcards.index' => [
                        'name' => trans('shop::admin.nav.giftcards'),
                        'permission' => 'shop.giftcards',
                    ],
                    'shop.admin.variables.index' => [
                        'name' => trans('shop::admin.nav.variables'),
                        'permission' => 'shop.packages',
                    ],
                    'shop.admin.payments.index' => [
                        'name' => trans('shop::admin.nav.payments'),
                        'permission' => 'shop.payments',
                    ],
                    'shop.admin.subscriptions.index' => [
                        'name' => trans('shop::admin.nav.subscriptions'),
                        'permission' => 'shop.payments',
                    ],
                    'shop.admin.purchases.index' => [
                        'name' => trans('shop::admin.nav.purchases'),
                        'permission' => 'shop.payments',
                    ],
                    'shop.admin.statistics' => [
                        'name' => trans('shop::admin.nav.statistics'),
                        'permission' => 'shop.payments',
                    ],
                ],
            ],
        ];
    }

    /**
     * Return the user navigations routes to register in the user menu.
     *
     * @return array<string, array<string, string>>
     */
    protected function userNavigation(): array
    {
        return [
            'shop' => [
                'route' => 'shop.profile',
                'name' => trans('shop::messages.profile.payments'),
                'icon' => 'bi bi-cash-coin',
            ],
        ];
    }
}
