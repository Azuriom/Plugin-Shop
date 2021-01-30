<?php

namespace Azuriom\Plugin\Shop\View\Composers;

use Azuriom\Extensions\Plugin\UserProfileCardComposer as CardComposer;

class UserProfileCardComposer extends CardComposer
{
    public function getCards()
    {
        return [
            'shop_gift_cards' => [
                'name' => trans('shop::messages.giftcards.add'),
                'view' => 'shop::giftcards.index',
            ],
        ];
    }
}
