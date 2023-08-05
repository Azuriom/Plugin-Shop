<?php

namespace Azuriom\Plugin\Shop\Models;

use Azuriom\Models\User as BaseUser;

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

    public static function ofUser(BaseUser $baseUser): self
    {
        return (new self())->newFromBuilder($baseUser->getAttributes());
    }
}
