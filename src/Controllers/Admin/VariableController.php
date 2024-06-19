<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Variable;
use Azuriom\Plugin\Shop\Requests\VariableRequest;

class VariableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('shop::admin.variables.index', [
            'variables' => Variable::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shop::admin.variables.create', ['types' => Variable::TYPES]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VariableRequest $request)
    {
        Variable::create($request->validated());

        return to_route('shop.admin.variables.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Variable $variable)
    {
        return view('shop::admin.variables.edit', [
            'variable' => $variable,
            'types' => Variable::TYPES,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VariableRequest $request, Variable $variable)
    {
        $variable->update($request->validated());

        return to_route('shop.admin.variables.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Variable $variable)
    {
        $variable->delete();

        return to_route('shop.admin.variables.index')
            ->with('success', trans('messages.status.success'));
    }
}
