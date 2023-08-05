<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Requests\CategoryRequest;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return to_route('shop.admin.packages.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shop::admin.categories.create', [
            'categories' => Category::parents()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        Category::create($request->validated());

        return to_route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Category $category)
    {
        return view('shop::admin.categories.edit', [
            'category' => $category,
            'categories' => Category::parents()->get()->except($category->id),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, Category $category)
    {
        $category->update($request->validated());

        return to_route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Category $category)
    {
        if ($category->packages()->exists() || $category->categories()->exists()) {
            return redirect()->back()->with('error', trans('shop::admin.categories.delete_error'));
        }

        $category->delete();

        return to_route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }
}
