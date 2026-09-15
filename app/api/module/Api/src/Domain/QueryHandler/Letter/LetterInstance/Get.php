<?php

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\QueryHandler\AbstractQueryByIdHandler;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

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
            // letterTodo carries todo_key, which the version does not. Two to-dos can share a
            // name -- FI01 and FI02 both read "You need to upload bank statements to your online
            // account" -- so the key is what makes a to-do identifiable in a picker.
            'letterTodoVersion' => [
                'letterTodo',
            ],
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

    /**
     * Adds requiringIssueCount to each to-do: how many of this letter's issues call for it.
     *
     * Computed here rather than exposed as an entity method, because entity methods are not
     * serialised — the caseworker screen could never read it. The count comes from a single pass
     * over the letter's issues (see LetterInstance::getTodoRequiringIssueCounts), not one walk per
     * to-do, which would be an N+1 against a lazy association.
     */
    #[\Override]
    public function handleQuery(QueryInterface $query)
    {
        /** @var LetterInstanceEntity $letterInstance */
        $letterInstance = $this->getRepo()->fetchUsingId($query);

        $result = $this->result($letterInstance, $this->bundle, $this->values);

        $counts = $letterInstance->getTodoRequiringIssueCounts();
        $data = $result->serialize();

        foreach ($data['letterInstanceTodos'] ?? [] as $index => $todo) {
            $versionId = $todo['letterTodoVersion']['id'] ?? null;
            $data['letterInstanceTodos'][$index]['requiringIssueCount'] = $counts[$versionId] ?? 1;
        }

        return $data;
    }
}
