<?php

namespace Permits\Controller\Config\Params;

/**
 * Holds parameter configs that are used regularly
 */
class ParamsConfig
{
    const NEW_APPLICATION = [
        'route' => [
            'type',
            'year'
        ],
    ];

    const CONFIRM_CHANGE = 'confirm-change';

    const ID_FROM_ROUTE = [
        'route' => [
            'id'
        ],
    ];
}
