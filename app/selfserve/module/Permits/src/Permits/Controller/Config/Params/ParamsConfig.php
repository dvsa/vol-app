<?php

namespace Permits\Controller\Config\Params;

/**
 * Holds parameter configs that are used regularly
 */
class ParamsConfig
{
    public const NEW_APPLICATION = [
        'route' => [
            'type',
            'year'
        ],
    ];

    public const ID_FROM_ROUTE = [
        'route' => [
            'id'
        ],
    ];
}
