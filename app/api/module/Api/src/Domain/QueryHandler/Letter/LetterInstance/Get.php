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
        'status',
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
                'letterIssueType',
                // parent issue id, so changing issues can pre-tick what's on the letter (VOL-7734)
                'letterIssue',
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
     * Adds requiringIssueCount to each to-do: how many of this letter's issues call for it, and
     * placementIssueIds: the issues it can be shown under, preferred first (VOL-7737).
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
        $placements = $letterInstance->getTodoPlacementIssueIds();
        $data = $result->serialize();

        foreach ($data['letterInstanceTodos'] ?? [] as $index => $todo) {
            // same key as LetterTodoVersion::getDedupeKey, built from the serialised row
            $todoId = $todo['letterTodoVersion']['letterTodo']['id'] ?? null;
            $key = $todoId !== null ? 'todo-' . $todoId : 'version-' . ($todo['letterTodoVersion']['id'] ?? '');
            $data['letterInstanceTodos'][$index]['requiringIssueCount'] = $counts[$key] ?? 1;

            // the issues the edit page can show this to-do under, preferred first (VOL-7737)
            $ownerId = $todo['letterInstanceIssue']['id'] ?? null;
            $data['letterInstanceTodos'][$index]['placementIssueIds'] = $placements[$key]
                ?? ($ownerId !== null ? [(int) $ownerId] : []);
        }

        return $data;
    }
}
