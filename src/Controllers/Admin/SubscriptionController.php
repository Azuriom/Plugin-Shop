<?php

namespace Azuriom\Plugin\Shop\Controllers\Admin;

use Azuriom\Http\Controllers\Controller;
use Azuriom\Plugin\Shop\Models\Subscription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $subscriptions = Subscription::with(['user', 'package'])
            ->when($search, fn (Builder $query) => $query->search($search))
            ->latest()
            ->paginate();

        return view('shop::admin.subscriptions.index', [
            'subscriptions' => $subscriptions,
            'search' => $search,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Subscription $subscription)
    {
        $subscription->load(['user', 'payments', 'package']);

        return view('shop::admin.subscriptions.show', ['subscription' => $subscription]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @throws \LogicException
     */
    public function destroy(Subscription $subscription)
    {
        $subscription->cancel();

        return to_route('shop.admin.subscriptions.index')
            ->with('success', trans('messages.status.success'));
    }
}
