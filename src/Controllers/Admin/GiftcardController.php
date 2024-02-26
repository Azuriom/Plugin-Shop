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
     */
    public function index()
    {
        $giftcards = Giftcard::with('payments')->get();

        $giftcards->each(fn (Giftcard $card) => $card->refreshBalance());

        return view('shop::admin.giftcards.index', ['giftcards' => $giftcards]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('shop::admin.giftcards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(GiftcardRequest $request)
    {
        Giftcard::create(array_merge($request->validated(), [
            'original_balance' => $request->input('balance'),
        ]));

        return to_route('shop.admin.giftcards.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Giftcard $giftcard)
    {
        $giftcard->load('payments')->refreshBalance();

        return view('shop::admin.giftcards.edit', ['giftcard' => $giftcard]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Giftcard $giftcard)
    {
        $validated = $this->validate($request, [
            'start_at' => ['required', 'date'],
            'expire_at' => ['required', 'date', 'after:start_at'],
        ]);

        $giftcard->update($validated);

        return to_route('shop.admin.giftcards.index')
            ->with('success', trans('messages.status.success'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Giftcard $giftcard)
    {
        $giftcard->delete();

        return to_route('shop.admin.giftcards.index')
            ->with('success', trans('messages.status.success'));
    }
}
