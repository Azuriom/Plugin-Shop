<?php

namespace Azuriom\Plugin\Shop\Models\Concerns;

trait IsBuyable
{
    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasQuantity(): bool
    {
        return $this->has_quantity ?? false;
    }

    public function getMaxQuantity(): int
    {
        return 1;
    }
}
