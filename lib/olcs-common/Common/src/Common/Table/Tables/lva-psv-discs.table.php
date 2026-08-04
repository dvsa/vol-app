<?php

use Common\Service\Table\Formatter\YesNo;

$translationPrefix = 'psv_discs.table';

return [
    'variables' => [
        'title' => $translationPrefix . '.title',
        'within_form' => true,
        'empty_message' => 'psv_discs.table.emptyMessage'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'label' => 'Request new discs',
                ],
                'replace' => [
                    'label' => 'Replace',
                    'class' => ' more-actions__item',
                    'requireRows' => true
                ],
                'void' => [
                    'label' => 'Remove',
                    'class' => ' more-actions__item',
                    'requireRows' => true
                ],
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
        'collapseAt' => 1,
        'row-disabled-callback' => static fn($row) => $row['ceasedDate'] !== null
    ],
    'columns' => [
        [
            'title' => $translationPrefix . '.discNo',
            'isNumeric' => true,
            'name' => 'discNo'
        ],
        [
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'title' => $translationPrefix . '.issuedDate',
            'name' => 'issuedDate'
        ],
        [
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'title' => $translationPrefix . '.ceasedDate',
            'name' => 'ceasedDate'
        ],
        [
            'title' => $translationPrefix . '.replacement',
            'name' => 'isCopy',
            'formatter' => YesNo::class
        ],
        [
            'title' => 'markup-table-th-remove-replace', //this is a view partial from olcs-common
            'type' => 'ActionLinks',
            'ariaDescription' => 'discNo',
            'isRemoveVisible' => static fn($data) => empty($data['ceasedDate']),
            'isReplaceVisible' => static fn($data) => empty($data['ceasedDate']),
            'deleteInputName' => 'table[action][void][%d]',
            'replaceInputName' => 'table[action][replace][%d]'
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true
        ]
    ]
];
