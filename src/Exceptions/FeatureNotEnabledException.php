<?php

declare(strict_types=1);

namespace Azuriom\Plugin\Shop\Exceptions;

use Exception;

class FeatureNotEnabledException extends Exception
{
    public function __construct()
    {
        parent::__construct(trans('Shop::messages.exceptions.feature_disabled_settings'));
    }
}
