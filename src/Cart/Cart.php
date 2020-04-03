<?php

namespace Azuriom\Plugin\Shop\Cart;

use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Session\Store as Session;

/**
 * Laravel cart.
 *
 * This class is based on https://github.com/Crinsane/LaravelShoppingcart, under MIT license.
 * Adapted to Azuriom since original package is not compatible with Laravel 6.x.
 *
 * @author Rob Gloudemans
 */
class Cart
{
    /**
     * The session manager instance.
     *
     * @var \Illuminate\Session\Store
     */
    private $session;

    /**
     * The items in the cart.
     *
     * @var \Illuminate\Support\Collection
     */
    private $items;

    /**
     * Create a new cart instance.
     *
     * @param  \Illuminate\Session\Store  $session
     */
    public function __construct(Session $session = null)
    {
        $this->session = $session;
        $this->items = collect([]);

        $sessionItems = $this->session ? $this->session->get('shop.cart', []) : [];
        $this->session->remove('shop.cart');

        foreach ($sessionItems as $sessionItem) {
            $refreshedPackage = Package::where("id", "=", $sessionItem->id)->get()->first();
            $this->add($refreshedPackage, $sessionItem->quantity);
        }
    }

    /**
     * Add an item to the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @param  int  $quantity
     */
    public function add(Buyable $buyable, int $quantity = 1)
    {
        $cartItem = $this->get($buyable);

        if ($cartItem !== null) {
            $cartItem->setQuantity($cartItem->quantity + $quantity);

            return;
        }

        $this->set($buyable, $quantity);
    }

    /**
     * Set the quantity of an item in the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @param  int  $quantity
     */
    public function set(Buyable $buyable, int $quantity = 1)
    {
        $cartItem = $this->get($buyable);

        if ($cartItem !== null) {
            $cartItem->setQuantity($quantity);

            return;
        }

        $id = $this->getItemId($buyable);

        $this->items->put($id, new CartItem($buyable, $id, $quantity));

        $this->saveToSession();
    }

    /**
     * Remove the cart item with the given rowId from the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     */
    public function remove(Buyable $buyable)
    {
        $this->items->forget($this->getItemId($buyable));

        $this->saveToSession();
    }

    /**
     * Get a cart item from the cart by its rowId.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @return \Azuriom\Plugin\Shop\Cart\CartItem|null
     */
    public function get(Buyable $buyable)
    {
        return $this->items->get($this->getItemId($buyable));
    }

    /**
     * Clear the current cart instance.
     *
     * @return void
     */
    public function clear()
    {
        $this->items = collect();

        $this->saveToSession();
    }

    public function isEmpty()
    {
        return $this->items->isEmpty();
    }

    /**
     * Get the content of the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function content()
    {
        return $this->items->values();
    }

    /**
     * Get the number of items in the cart.
     *
     * @return int|float
     */
    public function count()
    {
        return $this->content()->sum(function ($item) {
            return $item->quantity;
        });
    }

    /**
     * Get the total price of the items in the cart.
     *
     * @return float
     */
    public function total()
    {
        return $this->content()->sum(function (CartItem $cartItem) {
            return $cartItem->total();
        });
    }

    protected function getItemId(Buyable $buyable)
    {
        return class_basename($buyable).'-'.$buyable->getId();
    }

    public function type()
    {
        return strtoupper(class_basename($this->items->first()->buyable()));
    }

    protected function saveToSession()
    {
        if ($this->session) {
            $this->session->put('shop.cart', $this->items->toArray());
        }
    }
}
