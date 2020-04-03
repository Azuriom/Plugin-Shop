<?php

namespace Azuriom\Plugin\Shop\Events;

use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Queue\SerializesModels;

class PackageDelivered
{
    use SerializesModels;

    public $package;

    public $quantity;

    /**
     * Create a new event instance.
     *
     * @param  \Azuriom\Plugin\Shop\Models\Package  $package
     * @param  int  $quantity
     */
    public function __construct(Package $package, int $quantity = 1)
    {
        $this->package = $package;
        $this->quantity = $quantity;
    }
}
