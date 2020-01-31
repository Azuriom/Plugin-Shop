<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

use Azuriom\Models\User;

interface Buyable
{
    /**
     * Get the identifier of the buyable.
     *
     * @return int
     */
    public function getId();

    /**
     * Get the name of this buyable.
     *
     * @return string
     */
    public function getName();

    /**
     * Get the price of the buyable.
     *
     * @return float
     */
    public function getPrice();

    /**
     * Get the description of the buyable.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Deliver this buyable once it is paid.
     *
     * @param  User  $user
     * @param  int  $quantity
     * @return bool
     */
    public function deliver(User $user, int $quantity = 1);
}
