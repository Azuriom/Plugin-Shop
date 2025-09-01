<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\User as BaseUser;
use Azuriom\Plugin\Shop\Models\User as ShopUser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @property \Illuminate\Support\Collection|\Azuriom\Plugin\Shop\Models\PaymentItem[] $items
 */
class User extends BaseUser
{
    /**
     * Get all the payment items purchased by this user.
     */
    public function items()
    {
        return $this->hasManyThrough(PaymentItem::class, Payment::class);
    }

    public static function currentUserPurchases(): Collection
    {
        $user = shop_user();

        if ($user === null) {
            return collect();
        }

        return once(fn () => ShopUser::ofUser($user)
            ->items()
            ->where('buyable_type', 'shop.packages')
            ->whereHas('payment', fn (Builder $q) => $q->scopes('completed'))
            ->get());
    }

    public static function ofUser(BaseUser $baseUser): self
    {
        return (new self())->newFromBuilder($baseUser->getAttributes());
    }
}
