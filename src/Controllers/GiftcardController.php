<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Giftcard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GiftcardController extends Controller
{
    /**
     * Add a giftcard to the user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function add(Request $request)
    {
        $validated = $this->validate($request, [
            'code' => ['required'],
        ]);

        $giftcard = Giftcard::active()->firstWhere($validated);
        $user = $request->user();

        if ($giftcard === null || $giftcard->hasReachLimit($user)) {
            throw ValidationException::withMessages([
                'code' => trans('shop::messages.giftcards.error'),
            ]);
        }

        $user->addMoney($giftcard->amount);

        $giftcard->users()->attach($user);

        return redirect()->back()->with('success', trans('shop::messages.giftcards.success', [
            'money' => format_money($giftcard->amount),
        ]));
    }
}
