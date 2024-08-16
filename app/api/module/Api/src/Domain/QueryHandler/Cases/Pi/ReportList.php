<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Pi;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * PI Report List
 */
final class ReportList extends AbstractListQueryHandler
{
    protected $repoServiceName = 'PiHearing';
    protected bool $modifyTrafficAreasForRbac = true;

    protected $bundle = [
        'pi' => [
            'case' => [
                'licence' => [
                    'organisation',
                    'status'
                ],
                'transportManager' => [
                    'tmStatus',
                    'homeCd' => [
                        'person'
                    ]
                ]
            ]
        ],
        'venue' => [
            'address'
        ]
    ];
}
