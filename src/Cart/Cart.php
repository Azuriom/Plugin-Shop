<?php

namespace Azuriom\Plugin\Shop\Cart;

use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Coupon;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Contracts\Session\Session;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

/**
 * Represent a cart content, with the items and coupons.
 *
 * This class is originally inspired by https://github.com/Crinsane/LaravelShoppingcart, under MIT license.
 */
class Cart implements Arrayable
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
     * The coupons applied to the cart.
     *
     * @var \Illuminate\Support\Collection
     */
    private $coupons;

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
            $this->coupons = collect();

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
        $item = new CartItem($this, $buyable, $id, $quantity);

        if ($item->quantity > 0) {
            $this->items->put($id, $item);
        }

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

    public function getById(string $id)
    {
        return $this->items->get($id);
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
     * Get the total price of the items in the cart without
     * applying coupons discounts.
     *
     * @return float
     */
    public function originalTotal()
    {
        return $this->content()->sum(function (CartItem $cartItem) {
            return $cartItem->originalTotal();
        });
    }

    /**
     * Get the total price of the items in the cart after
     * applying coupons discounts.
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

    /**
     * Get the coupons applied to the cart.
     *
     * @return \Illuminate\Support\Collection
     */
    public function coupons()
    {
        return $this->coupons;
    }

    /**
     * Add a coupon to the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     */
    public function addCoupon(Coupon $coupon)
    {
        $this->coupons->put($coupon->id, $coupon);

        $this->save();
    }

    /**
     * Remove a coupon from the cart.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Coupon  $coupon
     */
    public function removeCoupon(Coupon $coupon)
    {
        $this->coupons->forget($coupon->id);

        $this->save();
    }

    /**
     * Remove all the coupons in the cart.
     */
    public function clearCoupon()
    {
        $this->coupons = collect();

        $this->save();
    }

    public function type()
    {
        return strtoupper(class_basename($this->items->first()->buyable()));
    }

    /**
     * Save the cart content to the associated session (if any).
     */
    public function save()
    {
        if ($this->session) {
            $this->session->put('shop.cart', $this->toArray());
        }
    }

    /**
     * Clear the items and the coupons and remove the current cart
     * from the session.
     */
    public function destroy()
    {
        $this->items = collect();
        $this->coupons = collect();

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
        $this->items = collect();

        $content = $session->get('shop.cart', []);

        if (! empty($content['coupons'])) {
            $this->coupons = Coupon::whereIn('code', $content['coupons'])->get()->keyBy('id');
        } else {
            $this->coupons = collect();
        }

        if (empty($content['items'])) {
            return;
        }

        collect($content['items'])->groupBy('type')->each(function (Collection $items, string $type) {
            /** @var \Illuminate\Database\Eloquent\Collection $models */
            $models = $type::findMany($items->pluck('id'))->keyBy('id');

            if ($type === Package::class) {
                $models->load('discounts');
            }

            $items->each(function ($item) use ($models) {
                if (! $models->has($item['id'])) {
                    return;
                }

                $cartItem = new CartItem($this, $models->get($item['id']), $item['itemId'], $item['quantity']);

                $this->items->put($item['itemId'], $cartItem);
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'items' => $this->items->toArray(),
            'coupons' => $this->coupons->pluck('code')->all(),
        ];
    }
}
