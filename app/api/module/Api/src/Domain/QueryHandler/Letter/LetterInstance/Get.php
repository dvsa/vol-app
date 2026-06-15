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
        'letterType' => [
            'category',
            'subCategory'
        ],
        'licence',
        'application',
        'case',
        'letterInstanceSections' => [
            'letterSectionVersion' => [
                'letterSectionVariant' => [
                    'letterSection'
                ]
            ]
        ],
        'letterInstanceIssues' => [
            'letterIssueVersion' => [
                'letterIssueType'
            ]
        ],
        'letterInstanceTodos' => [
            'letterTodoVersion',
            'letterInstanceIssue' => [
                'letterIssueVersion' => [
                    'letterIssueType',
                ],
            ],
        ],
        'letterInstanceAppendices' => [
            'letterAppendixVersion' => [
                'document'
            ]
        ]
    ];
}
