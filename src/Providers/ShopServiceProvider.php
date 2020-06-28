<?php

namespace Azuriom\Plugin\Shop\Providers;

use Azuriom\Extensions\Plugin\BasePluginServiceProvider;
use Azuriom\Models\Permission;
use Azuriom\Plugin\Shop\Payment\PaymentManager;
use Azuriom\Plugin\Shop\View\Composers\ShopAdminDashboardComposer;
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

        $this->app->singleton(PaymentManager::class, function () {
            return new PaymentManager();
        });
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

        $this->registerAdminNavigation();

        View::composer('admin.dashboard', ShopAdminDashboardComposer::class);

        Permission::registerPermissions(['shop.admin' => 'shop::admin.permissions.admin']);
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
                    'shop.admin.payments.index' => 'shop::admin.nav.payments',
                    'shop.admin.purchases.index' => 'shop::admin.nav.purchases',
                ],
            ],
        ];
    }
}
