<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
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
        return view('shop::admin.packages.index', [
            'categories' => Category::with('packages')->orderBy('position')->get(),
        ]);
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

            Category::whereKey($id)->update([
                'position' => $categoryPosition++,
            ]);

            $packagePosition = 1;

            foreach ($packages as $package) {
                Package::whereKey($package)->update([
                    'position' => $packagePosition++,
                    'category_id' => $id,
                ]);
            }
        }

        return $request->expectsJson() ? response()->json([
            'status' => 'success',
            'message' => trans('shop.packages.status.order-updated'),
        ]) : redirect()->route('shop::admin.packages.index');
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
        $servers = array_keys($request->input('servers', []));

        $package = new Package(Arr::except($request->validated(), 'image'));

        if ($request->hasFile('image')) {
            $package->storeImage($request->file('image'));
        }

        $package->save();
        $package->servers()->sync($servers);

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

        $servers = array_keys($request->input('servers', []));

        $package->update(Arr::except($request->validated(), 'image'));
        $package->servers()->sync($servers);

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
