<?php

namespace Azuriom\Plugin\Shop\Controllers;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Payment;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $payments = Payment::where('user_id', $user->id)
            ->scopes(['notPending', 'withRealMoney'])
            ->get();

        return view('shop::profile.index', [
            'user' => $user,
            'payments' => $payments,
        ]);
    }
}
