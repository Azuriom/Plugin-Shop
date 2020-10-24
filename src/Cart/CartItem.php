<?php

namespace Azuriom\Plugin\Shop\Cart;

use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Coupon;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Laravel cart item.
 *
 * This class is originally based on https://github.com/Crinsane/LaravelShoppingcart, under MIT license.
 */
class CartItem implements Arrayable
{
    /**
     * The cart where this item is stored.
     * The cart *may* not contain this item if it was removed.
     *
     * @var \Azuriom\Plugin\Shop\Cart\Cart
     */
    private $cart;

    /**
     * The ID of the cart item with the format '{model class}-{model id}'.
     *
     * @var string
     */
    public $itemId;

    /**
     * The ID of the item.
     *
     * @var int
     */
    public $id;

    /**
     * The quantity for this cart item.
     *
     * @var int
     */
    public $quantity;

    /**
     * The model class.
     *
     * @var string
     */
    public $type;

    /**
     * The associated model.
     *
     * @var \Azuriom\Plugin\Shop\Models\Concerns\Buyable
     */
    private $buyable;

    /**
     * Create a new item instance.
     *
     * @param  \Azuriom\Plugin\Shop\Cart\Cart  $cart
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @param  string  $itemId
     * @param  int  $quantity
     */
    public function __construct(Cart $cart, Buyable $buyable, string $itemId, int $quantity = 1)
    {
        $this->cart = $cart;
        $this->id = $buyable->id;
        $this->itemId = $itemId;
        $this->type = get_class($buyable);
        $this->buyable = $buyable;
        $this->setQuantity($quantity);
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param  int  $quantity
     */
    public function setQuantity(int $quantity)
    {
        $maxQuantity = $this->buyable->getMaxQuantity();

        $this->quantity = min($this->hasQuantity() ? $quantity : 1, $maxQuantity);

        if ($this->quantity <= 0) {
            $this->cart->remove($this->buyable);
        }
    }

    /**
     * Retrieve the buyable model.
     *
     * @return \Azuriom\Plugin\Shop\Models\Concerns\Buyable
     */
    public function buyable()
    {
        return $this->buyable;
    }

    public function hasQuantity()
    {
        return $this->buyable->hasQuantity();
    }

    public function maxQuantity()
    {
        return $this->buyable->getMaxQuantity();
    }

    public function name()
    {
        return $this->buyable->getName();
    }

    public function originalPrice()
    {
        return $this->buyable->getPrice();
    }

    public function price()
    {
        $package = $this->buyable;

        if (! $package instanceof Package) {
            return $this->originalPrice();
        }

        $coupons = $this->cart->coupons()
            ->filter(function (Coupon $coupon) use ($package) {
                return $coupon->isActiveOn($package);
            });

        return round($coupons->reduce(function ($price, Coupon $coupon) {
            return $price - ($coupon->discount / 100) * $price;
        }, $this->originalPrice()), 2);
    }

    public function originalTotal()
    {
        return $this->originalPrice() * $this->quantity;
    }

    public function total()
    {
        return $this->price() * $this->quantity;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'itemId' => $this->itemId,
            'type' => $this->type,
            'quantity' => $this->quantity,
        ];
    }
}
