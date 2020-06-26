<?php

namespace Azuriom\Plugin\Shop\Cart;

use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Illuminate\Contracts\Session\Session;
use Illuminate\Support\Collection;

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
     * The session where this cart is stored.
     *
     * @var \Illuminate\Contracts\Session\Session
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
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @deprecated Use Cart::fromSession() or Cart::empty()
     */
    public function __construct(Session $session = null)
    {
        $this->session = $session;

        if ($session === null) {
            $this->items = collect();

            return;
        }

        $this->loadFromSession($this->session);
    }

    /**
     * Add an item to the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @param  int  $quantity
     */
    public function add(Buyable $buyable, int $quantity = 1)
    {
        if ($quantity <= 0) {
            return;
        }

        $cartItem = $this->get($buyable);

        if ($cartItem === null) {
            $this->set($buyable, $quantity);

            return;
        }

        $cartItem->setQuantity($cartItem->quantity + $quantity);
        $this->save();
    }

    /**
     * Set the quantity of an item in the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @param  int  $quantity
     */
    public function set(Buyable $buyable, int $quantity = 1)
    {
        if ($quantity <= 0) {
            $this->remove($buyable);

            return;
        }

        $cartItem = $this->get($buyable);

        if ($cartItem !== null) {
            $cartItem->setQuantity($quantity);

            return;
        }

        $id = $this->getItemId($buyable);

        $this->items->put($id, new CartItem($buyable, $id, $quantity));

        $this->save();
    }

    /**
     * Remove the given model from cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     */
    public function remove(Buyable $buyable)
    {
        $this->items->forget($this->getItemId($buyable));

        $this->save();
    }

    /**
     * Get from the cart the cart items associated to this model.
     * Return null if this model is not in this cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @return \Azuriom\Plugin\Shop\Cart\CartItem|null
     */
    public function get(Buyable $buyable)
    {
        return $this->items->get($this->getItemId($buyable));
    }

    /**
     * Determine if a cart item is in the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @return bool
     */
    public function has(Buyable $buyable)
    {
        return $this->items->has($this->getItemId($buyable));
    }

    /**
     * Clear the current cart instance.
     *
     * @return void
     */
    public function clear()
    {
        $this->items = collect();

        $this->save();
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
     * @return int
     */
    public function count()
    {
        return $this->content()->sum('quantity');
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

    protected function save()
    {
        if ($this->session) {
            $this->session->put('shop.cart', $this->items->toArray());
        }
    }

    /**
     * Clear the items and remove the current cart from the session.
     */
    public function destroy()
    {
        $this->items = collect();

        if ($this->session) {
            $this->session->remove('shop.cart');
        }
    }

    /**
     * Create a new empty cart without associated session.
     *
     * @return self
     */
    public static function createEmpty()
    {
        return new self(null);
    }

    /**
     * Create a new cart instance and load the content of the associated session.
     *
     * @param  \Illuminate\Contracts\Session\Session  $session
     * @return self
     */
    public static function fromSession(Session $session)
    {
        return new self($session);
    }

    protected function loadFromSession(Session $session)
    {
        $items = $session->get('shop.cart', []);

        if (empty($items)) {
            return;
        }

        $this->items = collect($items)->groupBy('type')->flatMap(function (Collection $items, string $type) {
            /** @var \Illuminate\Database\Eloquent\Collection $models */
            $models = $type::findMany($items->pluck('id'))->keyBy('id');

            return $items->mapWithKeys(function ($item) use ($models) {
                if (! $models->has($item['id'])) {
                    return [];
                }

                $cartItem = new CartItem($models->get($item['id']), $item['itemId'], $item['quantity']);

                return [$item['itemId'] => $cartItem];
            });
        });
    }
}
