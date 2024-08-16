<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Report;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * @author Dmitry Golubev <d.e.golubev@gmail.com>
 */
class OpenList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'Cases';
    protected bool $modifyTrafficAreasForRbac = true;

    protected $bundle = [
        'licence' => [
            'organisation',
            'trafficArea',
            'status',
        ],
        'application' => [
            'status',
        ],
        'transportManager' => [
            'homeCd' => [
                'person' => [
                    'title',
                ],
            ],
        ],
        'categorys',
        'outcomes',
    ];
}
