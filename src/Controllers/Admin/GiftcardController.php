<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Azuriom\Plugin\Shop\Requests\GiftcardRequest;
use Illuminate\Http\Request;

class GiftcardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('shop::admin.giftcards.index', [
            'giftcards' => Giftcard::all(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('shop::admin.giftcards.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Azuriom\Plugin\Shop\Requests\GiftcardRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GiftcardRequest $request)
    {
        Giftcard::create(array_merge($request->validated(), [
            'original_balance' => $request->input('balance'),
        ]));

        return redirect()->route('shop.admin.giftcards.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Giftcard  $giftcard
     * @return \Illuminate\Http\Response
     */
    public function edit(Giftcard $giftcard)
    {
        return view('shop::admin.giftcards.edit', ['giftcard' => $giftcard]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Azuriom\Plugin\Shop\Models\Giftcard  $giftcard
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Giftcard $giftcard)
    {
        $validated = $this->validate($request, [
            'start_at' => ['required', 'date'],
            'expire_at' => ['required', 'date', 'after:start_at'],
        ]);

        $giftcard->update($validated);

        return redirect()->route('shop.admin.giftcards.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Giftcard  $giftcard
     * @return \Illuminate\Http\Response
     *
     * @throws \Exception
     */
    public function destroy(Giftcard $giftcard)
    {
        $giftcard->delete();

        return redirect()->route('shop.admin.giftcards.index')
            ->with('success', trans('messages.status.success'));
    }
}
