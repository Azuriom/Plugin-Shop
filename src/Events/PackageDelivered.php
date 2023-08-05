<?php

namespace Azuriom\Plugin\Shop\Events;

use Azuriom\Models\User;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Queue\SerializesModels;

class PackageDelivered
{
    use SerializesModels;

    public User $user;

    public Package $package;

    public int $quantity;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, Package $package, int $quantity = 1)
    {
        $this->user = $user;
        $this->package = $package;
        $this->quantity = $quantity;
    }
}
