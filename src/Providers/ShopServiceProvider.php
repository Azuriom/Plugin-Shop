<?php

namespace Azuriom\Plugin\Shop\Providers;

use Azuriom\Extensions\Plugin\AdminUserEditComposer;
use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Azuriom\Plugin\Shop\Models\Offer;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Azuriom\Plugin\Shop\View\Composers\ShopAdminDashboardComposer;
use Azuriom\Plugin\Shop\View\Composers\ShopAdminUserComposer;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;

class ShopServiceProvider extends BasePluginServiceProvider
{
    /**
     * Register any plugin services.
     *
     * @return void
     */
    public function register()
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
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->loadViews();

        $this->loadTranslations();

        $this->loadMigrations();

        $this->registerRouteDescriptions();

        $this->registerUserNavigation();

        $this->registerAdminNavigation();

        View::composer('admin.dashboard', ShopAdminDashboardComposer::class);

        if (class_exists(AdminUserEditComposer::class)) {
            View::composer('admin.users.edit', ShopAdminUserComposer::class);
        }

        Permission::registerPermissions(['shop.admin' => 'shop::admin.permissions.admin']);

        ActionLog::registerLogModels([
            Offer::class,
            Package::class,
            Gateway::class,
        ], 'shop::admin.logs');

        ActionLog::registerLogs('shop-giftcards.used', [
            'icon' => 'credit-card',
            'color' => 'info',
            'message' => 'shop::admin.logs.shop-giftcards.used',
            'model' => Giftcard::class,
        ]);
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array
     */
    protected function routeDescriptions()
    {
        return [
            'shop.home' => trans('shop::messages.title'),
        ];
    }

    /**
     * Return the admin navigations routes to register in the dashboard.
     *
     * @return array
     */
    protected function adminNavigation()
    {
        return [
            'shop' => [
                'name' => trans('shop::admin.nav.title'),
                'type' => 'dropdown',
                'icon' => 'bi bi-cart',
                'route' => 'shop.admin.*',
                'permission' => 'shop.admin',
                'items' => [
                    'shop.admin.settings' => trans('shop::admin.nav.settings'),
                    'shop.admin.packages.index' => trans('shop::admin.nav.packages'),
                    'shop.admin.gateways.index' => trans('shop::admin.nav.gateways'),
                    'shop.admin.offers.index' => trans('shop::admin.nav.offers'),
                    'shop.admin.discounts.index' => trans('shop::admin.nav.discounts'),
                    'shop.admin.coupons.index' => trans('shop::admin.nav.coupons'),
                    'shop.admin.giftcards.index' => trans('shop::admin.nav.giftcards'),
                    'shop.admin.payments.index' => trans('shop::admin.nav.payments'),
                    'shop.admin.purchases.index' => trans('shop::admin.nav.purchases'),
                    'shop.admin.statistics' => trans('shop::admin.nav.statistics'),
                ],
            ],
        ];
    }

    /**
     * Return the user navigations routes to register in the user menu.
     *
     * @return array
     */
    protected function userNavigation()
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
