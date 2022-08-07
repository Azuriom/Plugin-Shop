<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = $this->getCategories();

        if (! setting('shop.home.enabled', true) && ! $categories->isEmpty()) {
            return redirect()->route('shop.categories.show', $categories->first());
        }

        $message = setting('shop.home', trans('shop::messages.welcome'));

        return view('shop::categories.index', [
            'category' => null,
            'categories' => $categories,
            'displayHome' => true,
            'goal' => $this->getMonthGoal(),
            'topCustomer' => $this->getTopCustomer(),
            'recentPayments' => $this->getRecentPayments(),
            'welcome' => new HtmlString($message),
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $categories = $this->getCategories($category);

        $category->load('packages.discounts');

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
        ]);
    }

    protected function getMonthGoal()
    {
        if (! setting('shop.month_goal')) {
            return false;
        }

        $total = Payment::scopes(['completed', 'withRealMoney'])
            ->where('created_at', '>', now()->startOfMonth())
            ->sum('price');

        return round(($total / setting('shop.month_goal')) * 100, 2);
    }

    protected function getRecentPayments()
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

    protected function getTopCustomer()
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

    protected function getCategories(Category $current = null)
    {
        return Category::scopes(['parents', 'enabled'])
            ->with('categories')
            ->withCount('packages')
            ->get();
    }
}
