<?php

namespace Azuriom\Plugin\Shop\Cart;

use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Illuminate\Contracts\Support\Arrayable;

/**
 * Laravel cart item.
 *
 * This class is based on https://github.com/Crinsane/LaravelShoppingcart, under MIT license.
 * Adapted to Azuriom since original package is not compatible with Laravel 6.x.
 *
 * @author Rob Gloudemans
 */
class CartItem implements Arrayable
{
    /**
     * The ID of the cart item.
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
     * @param  \Azuriom\Plugin\Shop\Models\Concerns\Buyable  $buyable
     * @param  string  $itemId
     * @param  int  $quantity
     */
    public function __construct(Buyable $buyable, string $itemId, int $quantity = 1)
    {
        $this->id = $buyable->id;
        $this->itemId = $itemId;
        $this->type = get_class($buyable);
        $this->quantity = $quantity;
        $this->buyable = $buyable;
    }

    /**
     * Set the quantity for this cart item.
     *
     * @param  int  $quantity
     */
    public function setQuantity(int $quantity)
    {
        $this->quantity = $quantity;
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

    public function price()
    {
        return $this->buyable()->getPrice();
    }

    public function total()
    {
        return $this->price() * $this->quantity;
    }

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
