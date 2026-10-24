<?php

namespace Azuriom\Plugin\Shop\Enums;

/**
 * Whether a package can be purchased by the current user, or why it can't be.
 */
enum PurchaseStatus: string
{
    case Available = 'available';
    case Unavailable = 'unavailable';
    case Limit = 'limit';
    case Requirements = 'requirements';
    case Subscribed = 'subscribed';

    public function isAvailable(): bool
    {
        return $this === self::Available;
    }

    /**
     * Get the message explaining why the package can't be purchased.
     */
    public function message(): string
    {
        return trans('shop::messages.packages.'.$this->value);
    }
}
