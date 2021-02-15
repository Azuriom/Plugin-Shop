<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Payment;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $category = Category::scopes(['parents', 'enabled'])->first();

        if ($category === null) {
            return view('shop::categories.index', [
                'goal' => $this->getMonthGoal(),
            ]);
        }

        return $this->show($category);
    }

    /**
     * Display the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        $categories = Category::scopes(['parents', 'enabled'])
            ->with('categories')
            ->withCount('packages')
            ->get()
            ->filter(function (Category $cat) use ($category) {
                return $cat->is($category) || ! $cat->categories->isEmpty() || $cat->packages_count > 0;
            });

        $category->load('packages.discounts');

        foreach ($category->packages as $package) {
            $package->setRelation('category', $category);
        }

        return view('shop::categories.show', [
            'category' => $category,
            'categories' => $categories,
            'goal' => $this->getMonthGoal(),
        ]);
    }

    protected function getMonthGoal()
    {
        if (! setting('shop.month-goal')) {
            return false;
        }

        $total = Payment::scopes(['completed', 'withRealMoney'])
            ->where('created_at', '>', now()->startOfMonth())
            ->sum('price');

        return round(($total / setting('shop.month-goal')) * 100, 2);
    }
}
