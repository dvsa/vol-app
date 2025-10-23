<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;

/**
 * Get LetterInstance by ID
 */
class Get extends AbstractQueryByIdHandler
{
    protected $repoServiceName = 'LetterInstance';
    
    protected $bundle = [
        'letterType',
        'licence',
        'application',
        'case',
        'sections' => [
            'letterSection' => [
                'currentVersion'
            ]
        ],
        'issues' => [
            'letterIssue' => [
                'currentVersion'
            ]
        ],
        'todos' => [
            'letterTodo' => [
                'currentVersion'
            ]
        ],
        'appendices' => [
            'letterAppendix' => [
                'currentVersion'
            ]
        ]
    ];
}
