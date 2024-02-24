<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

use Azuriom\Plugin\Shop\Models\PaymentItem;

interface Buyable
{
    /**
     * Get the identifier of the buyable.
     */
    public function getId(): int;

    /**
     * Get the name of this buyable.
     */
    public function getName(): string;

    /**
     * Get the price of the buyable.
     */
    public function getPrice(): float;

    /**
     * Get the description of the buyable.
     */
    public function getDescription(): string;

    /**
     * Get whether the buyable can be purchased multiple times.
     */
    public function hasQuantity(): bool;

    /**
     * Get the maximum purchase quantity that the current user can purchase.
     */
    public function getMaxQuantity(): int;

    /**
     * Deliver this buyable once it is paid.
     */
    public function deliver(PaymentItem $item): void;
}
