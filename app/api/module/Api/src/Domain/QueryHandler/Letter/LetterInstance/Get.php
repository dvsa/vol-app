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

        $issuesById = [];
        foreach ($letterInstance->getLetterInstanceIssues() as $issue) {
            $issuesById[(int) $issue->getId()] = $issue;
        }
        $todosById = [];
        foreach ($letterInstance->getLetterInstanceTodos() as $todo) {
            $todosById[(int) $todo->getId()] = $todo;
        }

        $sectionsById = [];
        foreach ($letterInstance->getLetterInstanceSections() as $section) {
            $sectionsById[(int) $section->getId()] = $section;
        }

        $counts = $letterInstance->getTodoRequiringIssueCounts();
        $placements = $letterInstance->getTodoPlacementIssueIds();
        $data = $result->serialize();

        // the letter's own rule for whether it can still change (VOL-7735), so pages don't copy it
        $data['isOpenForChanges'] = $letterInstance->isOpenForChanges();
        $data['isSent'] = (bool) $letterInstance->isSent();

        // a section with no wording that asks for none isn't offered for editing, like the interim
        // paragraph when Interim isn't ticked
        foreach ($data['letterInstanceSections'] ?? [] as $index => $row) {
            $section = $sectionsById[(int) ($row['id'] ?? 0)] ?? null;
            $data['letterInstanceSections'][$index]['nothingToEdit'] = $section?->hasNothingToEdit() ?? false;
        }

        // the effective heading and "Input required", worked out by the entities (VOL-7735)
        foreach ($data['letterInstanceIssues'] ?? [] as $index => $row) {
            $issue = $issuesById[(int) ($row['id'] ?? 0)] ?? null;
            $data['letterInstanceIssues'][$index]['effectiveHeading'] = $issue?->getHeading();
            $data['letterInstanceIssues'][$index]['displayName'] = $issue?->getDisplayName();
            $data['letterInstanceIssues'][$index]['isOther'] = $issue?->isOther() ?? false;
            $data['letterInstanceIssues'][$index]['inputPending'] = $issue?->isInputPending() ?? false;
        }

        foreach ($data['letterInstanceTodos'] ?? [] as $index => $todo) {
            $entity = $todosById[(int) ($todo['id'] ?? 0)] ?? null;
            $data['letterInstanceTodos'][$index]['isCustom'] = $entity?->isCustom() ?? false;
            $data['letterInstanceTodos'][$index]['customIndex'] = $entity?->getCustomIndex();
            $data['letterInstanceTodos'][$index]['customLabel'] = $entity?->getCustomLabel();
            $data['letterInstanceTodos'][$index]['inputPending'] = $entity?->isInputPending() ?? false;

            // same key as LetterTodoVersion::getDedupeKey, built from the serialised row
            $todoId = $todo['letterTodoVersion']['letterTodo']['id'] ?? null;
            $key = $todoId !== null ? 'todo-' . $todoId : 'version-' . ($todo['letterTodoVersion']['id'] ?? '');
            $data['letterInstanceTodos'][$index]['requiringIssueCount'] = $counts[$key] ?? 1;

            // the issues the edit page can show this to-do under, preferred first (VOL-7737)
            $data['letterInstanceTodos'][$index]['placementIssueIds'] = $placements[(int) ($todo['id'] ?? 0)] ?? [];
        }

        return $data;
    }
}
