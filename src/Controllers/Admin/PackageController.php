<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Role;
use Azuriom\Models\Server;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Models\Variable;
use Azuriom\Plugin\Shop\Requests\PackageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = Category::parents()
            ->with(['categories.packages', 'categories.categories', 'packages'])
            ->get();

        return view('shop::admin.packages.index', ['categories' => $categories]);
    }

    /**
     * Update the order of the resources.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function updateOrder(Request $request)
    {
        $this->validate($request, [
            'categories' => ['required', 'array'],
        ]);

        $categories = $request->input('categories');

        $categoryPosition = 1;

        foreach ($categories as $category) {
            $id = $category['id'];
            $packages = $category['packages'] ?? [];
            $subCategories = $category['categories'] ?? [];

            Category::whereKey($id)->update([
                'position' => $categoryPosition++,
                'parent_id' => null,
            ]);

            $packagePosition = 1;

            foreach ($subCategories as $subCategory) {
                Category::whereKey($subCategory['id'])->update([
                    'position' => $packagePosition++,
                    'parent_id' => $id,
                ]);

                foreach ($subCategory['packages'] ?? [] as $package) {
                    Package::whereKey($package)->update([
                        'position' => $packagePosition++,
                        'category_id' => $subCategory['id'],
                    ]);
                }
            }

            foreach ($packages as $package) {
                Package::whereKey($package)->update([
                    'position' => $packagePosition++,
                    'category_id' => $id,
                ]);
            }
        }

        return response()->json([
            'message' => trans('shop::admin.packages.updated'),
        ]);
    }

    /**
     * Duplicate the specified package.
     */
    public function duplicate(Package $package)
    {
        $replicate = $package->replicate();

        $replicate->fill(['image' => null])->save();

        return to_route('shop.admin.packages.edit', ['package' => $replicate]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shop::admin.packages.create', [
            'categories' => Category::with('packages')->get(),
            'roles' => Role::where('is_admin', false)->get(),
            'servers' => Server::executable()->get(),
            'variables' => Variable::all(),
            'commandTriggers' => Package::COMMAND_TRIGGERS,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PackageRequest $request)
    {
        $package = Package::create(Arr::except($request->validated(), [
            'image', 'file', 'variables',
        ]));

        if ($request->hasFile('image')) {
            $package->storeImage($request->file('image'), true);
        }

        if ($request->hasFile('file')) {
            $package->storeFile($request->file('file'), true);
        }

        $package->variables()->sync($request->input('variables', []));

        return to_route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        return view('shop::admin.packages.edit', [
            'package' => $package,
            'categories' => Category::with('packages')->get(),
            'roles' => Role::where('is_admin', false)->get(),
            'servers' => Server::executable()->get(),
            'variables' => Variable::all(),
            'commandTriggers' => Package::COMMAND_TRIGGERS,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PackageRequest $request, Package $package)
    {
        if ($request->hasFile('image')) {
            $package->storeImage($request->file('image'));
        }

        $package->update(Arr::except($request->validated(), [
            'image', 'file', 'variables',
        ]));

        if ($request->hasFile('file')) {
            $package->storeFile($request->file('file'), true);
        }

        $package->variables()->sync($request->input('variables', []));

        return to_route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Package $package)
    {
        if ($package->subscriptions()->scopes('active')->exists()) {
            return to_route('shop.admin.packages.index')
                ->with('error', trans('shop::admin.subscriptions.error'));
        }

        $package->delete();

        return to_route('shop.admin.packages.index')
            ->with('success', trans('messages.status.success'));
    }
}
