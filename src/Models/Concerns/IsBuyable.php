<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

trait IsBuyable
{
    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getPrice()
    {
        return $this->price;
    }

    public function getDescription()
    {
        return $this->description;
    }
}
