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

    const ID_FROM_ROUTE = [
        'route' => [
            'id'
        ],
    ];
}
