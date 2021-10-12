<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Models\Role;
use Azuriom\Models\Server;
use Azuriom\Plugin\Shop\Models\Category;
use Azuriom\Plugin\Shop\Models\Package;
use Azuriom\Plugin\Shop\Requests\PackageRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::parents()->with('packages')->get();

        return view('shop::admin.packages.index', ['categories' => $categories]);
    }

    /**
     * Update the order of the resources.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
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
            'message' => trans('shop::admin.packages.status.order-updated'),
        ]);
    }

    /**
     * Clone the specified package.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function clone(Package $package)
    {
        $clone = $package->replicate();

        $clone->update(['image' => null]);

        return redirect()->route('shop.admin.packages.edit', $clone);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shop::admin.packages.create', [
            'categories' => Category::all(),
            'roles' => Role::where('is_admin', false)->get(),
            'servers' => Server::executable()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\PackageRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PackageRequest $request)
    {
        $data = $request->validated();
        $package = new Package(Arr::except($data, ['translations','image']));

        set_spatie_translations($package, $data['translations']);

        $package->save();

        if ($request->hasFile('image')) {
            $package->storeImage($request->file('image'), true);
        }

        $package->servers()->sync($request->input('servers', []));

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('shop::admin.packages.status.created'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function edit(Package $package)
    {
        return view('shop::admin.packages.edit', [
            'package' => $package,
            'categories' => Category::all(),
            'roles' => Role::where('is_admin', false)->get(),
            'servers' => Server::executable()->get(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\PackageRequest  $request
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     */
    public function update(PackageRequest $request, Package $package)
    {
        if ($request->hasFile('image')) {
            $package->storeImage($request->file('image'));
        }

        $data = $request->validated();
        set_spatie_translations($package, $data['translations']);

        $package->update(Arr::except($data, ['translations','image']));

        $package->servers()->sync($request->input('servers', []));

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('shop::admin.packages.status.updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Package $package)
    {
        $package->delete();

        return redirect()->route('shop.admin.packages.index')
            ->with('success', trans('shop::admin.packages.status.deleted'));
    }
}
