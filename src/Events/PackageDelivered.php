<?php

namespace Azuriom\Plugin\Shop\Events;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Queue\SerializesModels;

class PackageDelivered
{
    use SerializesModels;

    public $user;

    public $package;

    public $quantity;

    /**
     * Create a new event instance.
     *
     * @param  \Azuriom\Models\User  $user
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @param  int  $quantity
     */
    public function __construct(User $user, Package $package, int $quantity = 1)
    {
        $this->user = $user;
        $this->package = $package;
        $this->quantity = $quantity;
    }
}
