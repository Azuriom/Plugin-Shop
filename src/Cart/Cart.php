<?php

namespace Azuriom\Plugin\Shop\Cart;

use Azuriom\Plugin\Shop\Models\Concerns\Buyable;
use Azuriom\Plugin\Shop\Models\Coupon;
use Azuriom\Plugin\Shop\Models\Giftcard;
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
     */
    private ?Session $session;

    /**
     * The items in the cart.
     */
    private Collection $items;

    /**
     * The coupons applied to the cart.
     */
    private Collection $coupons;

    /**
     * The giftcards applied to the cart.
     */
    private Collection $giftcards;

    /**
     * Create a new cart instance.
     */
    private function __construct(?Session $session = null)
    {
        $this->session = $session;

        if ($session === null) {
            $this->items = collect();
            $this->coupons = collect();
            $this->giftcards = collect();

            return;
        }

        $this->loadFromSession($this->session);
    }

    /**
     * Add an item to the cart.
     */
    public function add(Buyable $buyable, int $quantity = 1, ?float $userPrice = null): CartItem
    {
        if ($quantity <= 0) {
            return $this->set($buyable, $quantity, $userPrice);
        }

        $cartItem = $this->get($buyable);

        if ($cartItem === null) {
            return $this->set($buyable, $quantity, $userPrice);
        }

        $cartItem->setQuantity($cartItem->quantity + $quantity);
        $cartItem->userPrice = $userPrice ?? $cartItem->userPrice;

        $this->save();

        return $cartItem;
    }

    /**
     * Set the quantity of an item in the cart.
     */
    public function set(Buyable $buyable, int $quantity = 1, ?float $userPrice = null): CartItem
    {
        if ($quantity <= 0) {
            $this->remove($buyable);

            return new CartItem($this, $buyable, $this->getItemId($buyable), 0);
        }

        $item = $this->get($buyable);

        if ($item !== null) {
            $item->setQuantity($quantity);
            $item->userPrice = $userPrice ?? $item->userPrice;

            return $item;
        }

        $id = $this->getItemId($buyable);
        $item = new CartItem($this, $buyable, $id, $quantity);
        $item->userPrice = $userPrice ?? $item->userPrice;

        if ($item->quantity > 0) {
            $this->items->put($id, $item);
        }

        $this->save();

        return $item;
    }

    /**
     * Remove the given model from cart.
     */
    public function remove(Buyable $buyable): void
    {
        $this->items->forget($this->getItemId($buyable));

        $this->save();
    }

    /**
     * Get the cart item associated with the given model.
     */
    public function get(Buyable $buyable): ?CartItem
    {
        return $this->items->get($this->getItemId($buyable));
    }

    /**
     * Get a cart item by its id.
     */
    public function getById(string $id): ?CartItem
    {
        return $this->items->get($id);
    }

    /**
     * Determine if a cart item is in the cart.
     */
    public function has(Buyable $buyable): bool
    {
        return $this->items->has($this->getItemId($buyable));
    }

    /**
     * Clear the current cart content.
     */
    public function clear(): void
    {
        $this->items = collect();

        $this->save();
    }

    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }

    /**
     * Get the content of the cart.
     */
    public function content(): Collection
    {
        return $this->items->values();
    }

    /**
     * Get the number of items in the cart.
     */
    public function count(): int
    {
        return $this->content()->sum('quantity');
    }

    /**
     * Get the total price of the items in the cart without
     * applying coupons or discounts.
     */
    public function originalTotal(): float
    {
        return $this->content()->sum(fn (CartItem $item) => $item->originalTotal());
    }

    /**
     * Get the total price of the items in the cart after
     * applying discounts and percentage coupons.
     */
    public function total(): float
    {
        return $this->itemsPrice()->sum(fn (array $item) => $item['price']);
    }

    public function itemsPrice(): Collection
    {
        // Store remaining amounts for fixed-price coupons
        $remaining = $this->coupons
            ->where('is_fixed', true)
            ->pluck('discount', 'id');

        return $this->content()->map(function (CartItem $item) use ($remaining) {
            $package = $item->buyable();

            if (! $package instanceof Package) {
                return [
                    'item' => $item,
                    'price' => $item->total(),
                    'unit_price' => $item->price(),
                ];
            }

            $total = $this->coupons()
                ->where('is_fixed', true)
                ->filter(fn (Coupon $coupon) => $coupon->isActiveOn($package))
                ->reduce(function ($price, Coupon $coupon) use ($remaining) {
                    $discount = $remaining->get($coupon->id, 0);
                    $remaining->put($coupon->id, max($discount - $price, 0));

                    return max($price - $discount, 0);
                }, $item->total());

            return [
                'item' => $item,
                'price' => $total,
                'unit_price' => $total / $item->quantity,
            ];
        });
    }

    /**
     * Get the final total price of the items in the cart after
     * applying discounts, percentage coupons and giftcards.
     */
    public function payableTotal(): float
    {
        return $this->giftcards
            ->filter(fn (Giftcard $card) => $card->isActive())
            ->reduce(function ($total, Giftcard $card) {
                return max($total - $card->balance, 0);
            }, $this->total());
    }

    /**
     * Get the coupons applied to the cart.
     */
    public function coupons(): Collection
    {
        return $this->coupons;
    }

    /**
     * Add a coupon to the cart.
     */
    public function addCoupon(Coupon $coupon): void
    {
        $this->coupons->put($coupon->id, $coupon);

        $this->save();
    }

    /**
     * Remove a coupon from the cart.
     */
    public function removeCoupon(Coupon $coupon): void
    {
        $this->coupons->forget($coupon->id);

        $this->save();
    }

    /**
     * Get all the giftcards applied to the cart.
     */
    public function giftcards(): Collection
    {
        return $this->giftcards;
    }

    /**
     * Add a new giftcard to the cart.
     */
    public function addGiftcard(Giftcard $giftcard): void
    {
        $this->giftcards->put($giftcard->id, $giftcard);

        $this->save();
    }

    /**
     * Remove a giftcard from the cart.
     */
    public function removeGiftcard(Giftcard $giftcard): void
    {
        $this->giftcards->forget($giftcard->id);

        $this->save();
    }

    /**
     * Remove all the coupons in the cart.
     */
    public function clearCoupons(): void
    {
        $this->coupons = collect();

        $this->save();
    }

    /**
     * Save the cart content to the associated session (if any).
     */
    public function save(): void
    {
        $this->session?->put('shop.cart', $this->toArray());
    }

    /**
     * Clear the items, coupons and giftcard from the cart.
     */
    public function destroy(): void
    {
        $this->items = collect();
        $this->coupons = collect();
        $this->giftcards = collect();

        $this->session?->remove('shop.cart');
    }

    /**
     * Create a new empty cart without an associated session.
     */
    public static function createEmpty(): self
    {
        return new self(null);
    }

    /**
     * Create a new cart instance and load the content from the given session.
     */
    public static function fromSession(Session $session): self
    {
        return new self($session);
    }

    protected function getItemId(Buyable $buyable): string
    {
        return class_basename($buyable).'-'.$buyable->getId();
    }

    protected function loadFromSession(Session $session): void
    {
        $this->items = collect();

        $content = $session->get('shop.cart', []);

        if (! empty($content['coupons'])) {
            $this->coupons = Coupon::whereIn('code', $content['coupons'])->get()->keyBy('id');
        } else {
            $this->coupons = collect();
        }

        if (! empty($content['giftcards'])) {
            $this->giftcards = Giftcard::whereIn('code', $content['giftcards'])->get()->keyBy('id');
        } else {
            $this->giftcards = collect();
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

                $buyable = $models->get($item['id']);
                $itemId = $item['itemId'];
                $quantity = $item['quantity'];
                $variables = $item['variables'] ?? [];

                $cartItem = new CartItem($this, $buyable, $itemId, $quantity, $variables);

                if (($userPrice = ($item['userPrice'] ?? null)) !== null) {
                    $cartItem->userPrice = $userPrice;
                }

                $this->items->put($item['itemId'], $cartItem);
            });
        });
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        return [
            'items' => $this->items->toArray(),
            'coupons' => $this->coupons->pluck('code')->all(),
            'giftcards' => $this->giftcards->pluck('code')->all(),
        ];
    }
}
