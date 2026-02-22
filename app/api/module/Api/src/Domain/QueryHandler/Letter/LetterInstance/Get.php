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
        'letterInstanceSections' => [
            'letterSectionVersion'
        ],
        'letterInstanceIssues' => [
            'letterIssueVersion' => [
                'letterIssueType'
            ]
        ],
        'letterInstanceTodos' => [
            'letterTodoVersion'
        ],
        'letterInstanceAppendices' => [
            'letterAppendixVersion' => [
                'document'
            ]
        ]
    ];
}
