<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\Translate;
use Common\Service\Table\Formatter\YesNo;

$translationPrefix = 'entity-view-table-conditions-undertakings.table';

return [
    'variables' => [],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => $translationPrefix . '.description',
            'name' => 'notes'
        ],
        [
            'title' => $translationPrefix . '.type',
            'formatter' => Translate::class,
            'name' => 'conditionType->description'
        ],
        [
            'title' => $translationPrefix . '.dateAdded',
            'name' => 'createdOn',
            'formatter' => Date::class
        ],
        [
            'title' => $translationPrefix  . '.fulfilled',
            'formatter' => YesNo::class,
            'name' => 'isFulfilled'
        ],
        [
            'title' => $translationPrefix . '.status',
            'formatter' => fn($data, $col) => $this->translator->translate(
                'common.table.status.' .
                ($data['isDraft'] === 'Y' ? 'draft' : 'approved')
            )
        ]
    ]
];
