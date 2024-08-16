<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractListQueryHandler;

/**
 * Record List associated to Data Retention Rule
 */
class Records extends AbstractListQueryHandler
{
    protected $repoServiceName = 'DataRetention';

    protected $bundle = [
        'assignedTo' => [
            'contactDetails' => [
                'person' => [
                    'forename',
                    'familyName'
                ]
            ]
        ]
    ];
}
