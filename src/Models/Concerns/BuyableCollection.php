<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

use Azuriom\Plugin\Shop\Models\Offer;
use Azuriom\Plugin\Shop\Models\Package;
use Illuminate\Support\Collection;

class BuyableCollection extends Collection
{
    /**
     * The buyables type.
     *
     * @var string
     */
    private $type;

    /**
     * BuyableCollection constructor.
     *
     * @param $type
     */
    public function __construct($type)
    {
        $this->type = $type;
    }

    public function serializeBuyables()
    {
        return $this->mapWithKeys(function ($quantity, $el) {
            return [$el->id => $quantity];
        });
    }

    public function getModels()
    {
        $type = $this->type === 'PACKAGE' ? Package::class : Offer::class;

        $type::findMany($this->keys());

        return $this->mapWithKeys(function ($quantity, $el) {
            return [$el->id => $quantity];
        });
    }
}
