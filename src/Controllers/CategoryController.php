<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = $this->getCategories();

        if (! setting('shop.home.enabled', true) && ! $categories->isEmpty()) {
            request()->session()->reflash();

            return to_route('shop.categories.show', $categories->first());
        }

        $message = setting('shop.home', trans('shop::messages.welcome'));

        return view('shop::categories.index', [
            'category' => null,
            'categories' => $categories,
            'displayHome' => true,
            'goal' => $this->getMonthGoal(),
            'topCustomer' => $this->getTopCustomer(),
            'recentPayments' => $this->getRecentPayments(),
            'displaySidebarAmount' => setting('shop.display_amount', true),
            'welcome' => new HtmlString($message),
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Category $category)
    {
        $categories = $this->getCategories();

        $category->load(['packages' => function (Builder $query) {
            $query->with('discounts')->scopes(['enabled']);
        }]);

        foreach ($category->packages as $package) {
            $package->setRelation('category', $category);
        }

        return view('shop::categories.show', [
            'category' => $category,
            'categories' => $categories,
            'displayHome' => setting('shop.home.enabled', true),
            'goal' => $this->getMonthGoal(),
            'topCustomer' => $this->getTopCustomer(),
            'recentPayments' => $this->getRecentPayments(),
            'displaySidebarAmount' => setting('shop.display_amount', true),
        ]);
    }

    protected function getMonthGoal(): float
    {
        if (! setting('shop.month_goal')) {
            return false;
        }

        $total = Payment::scopes(['completed', 'withRealMoney'])
            ->where('created_at', '>', now()->startOfMonth())
            ->sum('price');

        return round(($total / setting('shop.month_goal')) * 100, 2);
    }

    protected function getRecentPayments(): ?Collection
    {
        $maxPayments = (int) setting('shop.recent_payments', 0);

        if ($maxPayments === 0) {
            return null;
        }

        return Payment::scopes(['completed', 'withRealMoney'])
            ->with('user')
            ->latest()
            ->take($maxPayments)
            ->get();
    }

    protected function getTopCustomer(): ?object
    {
        if (! setting('shop.top_customer', false)) {
            return null;
        }

        $column = Payment::query()->getGrammar()->wrap('price');

        $payment = Payment::scopes(['completed', 'withRealMoney'])
            ->select(['user_id', DB::raw("sum({$column}) as aggregate")])
            ->where('created_at', '>', now()->startOfMonth())
            ->groupBy('user_id')
            ->orderByDesc('aggregate')
            ->first();

        if ($payment === null || $payment->user === null) {
            return null;
        }

        return (object) [
            'user' => $payment->user,
            'total' => $payment->aggregate,
        ];
    }

    protected function getCategories(): Collection
    {
        return Category::scopes(['parents', 'enabled'])
            ->with('categories')
            ->withCount('packages')
            ->get();
    }
}
