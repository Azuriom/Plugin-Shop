<?php

namespace Azuriom\Plugin\Shop\Providers;

use Azuriom\Models\ActionLog;
use Azuriom\Models\Permission;
use Illuminate\Support\Facades\View;
use Azuriom\Plugin\Shop\Models\Offer;
use Azuriom\Plugin\Shop\Models\Gateway;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Illuminate\Database\Eloquent\Relations\Relation;
use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Plugin\Shop\View\Composers\UserProfileCardComposer;
use Azuriom\Plugin\Shop\View\Composers\ShopAdminDashboardComposer;

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

        View::composer('profile.index', UserProfileCardComposer::class);

        Permission::registerPermissions(['shop.admin' => 'shop::admin.permissions.admin']);

        ActionLog::registerLogModels([
            Offer::class,
            Package::class,
            Gateway::class,
        ], 'shop::admin.logs');
    }

    /**
     * Returns the routes that should be able to be added to the navbar.
     *
     * @return array
     */
    protected function routeDescriptions()
    {
        return [
            'shop.home' => 'shop::messages.title',
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
                'name' => 'shop::admin.nav.title',
                'type' => 'dropdown',
                'icon' => 'fas fa-shopping-cart',
                'route' => 'shop.admin.*',
                'permission' => 'shop.admin',
                'items' => [
                    'shop.admin.settings' => 'shop::admin.nav.settings',
                    'shop.admin.packages.index' => 'shop::admin.nav.packages',
                    'shop.admin.gateways.index' => 'shop::admin.nav.gateways',
                    'shop.admin.offers.index' => 'shop::admin.nav.offers',
                    'shop.admin.discounts.index' => 'shop::admin.nav.discounts',
                    'shop.admin.coupons.index' => 'shop::admin.nav.coupons',
                    'shop.admin.giftcards.index' => 'shop::admin.nav.giftcards',
                    'shop.admin.payments.index' => 'shop::admin.nav.payments',
                    'shop.admin.purchases.index' => 'shop::admin.nav.purchases',
                    'shop.admin.statistics' => 'shop::admin.nav.statistics',
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
                'name' => 'shop::messages.profile.payments',
            ],
        ];
    }
}
