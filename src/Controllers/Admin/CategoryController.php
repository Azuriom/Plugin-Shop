<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Requests\CategoryRequest;

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
        return view('shop::admin.categories.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\CategoryRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('shop::admin.categories.status.created'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        return view('shop::admin.categories.edit', ['category' => $category]);
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
        $category->update($request->validated());

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('shop::admin.categories.status.updated'));
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
        if ($category->packages()->exists()) {
            return redirect()->back()->with('error', trans('shop::admin.categories.status.delete-items'));
        }

        $category->delete();

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('shop::admin.categories.status.deleted'));
    }
}
