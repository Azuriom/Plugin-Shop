<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Support\HtmlString;

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

    protected function getCategories(Category $current = null)
    {
        return Category::scopes(['parents', 'enabled'])
            ->with('categories')
            ->withCount('packages')
            ->get();
    }
}
