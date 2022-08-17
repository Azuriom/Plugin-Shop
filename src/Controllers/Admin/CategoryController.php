<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Requests\CategoryRequest;
use Illuminate\Support\Arr;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return redirect()->route('shop.admin.packages.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shop::admin.categories.create', [
            'categories' => Category::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\CategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        $category = Category::create(Arr::except($request->validated(), 'image'));

        if ($request->hasFile('image')) {
            $category->storeImage($request->file('image'), true);
        }

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view('shop::admin.categories.edit', [
            'category' => $category,
            'categories' => Category::all()->except($category->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\CategoryRequest  $request
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(CategoryRequest $request, Category $category)
    {
        if ($request->hasFile('image')) {
            $category->storeImage($request->file('image'));
        }

        $category->update(Arr::except($request->validated(), 'image'));

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Category $category)
    {
        if ($category->packages()->exists() || $category->categories()->exists()) {
            return redirect()->back()->with('error', trans('shop::admin.categories.delete_error'));
        }

        $category->delete();

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }
}
