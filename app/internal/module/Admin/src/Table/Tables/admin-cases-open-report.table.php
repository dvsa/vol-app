<?php

return [
    'variables' => [
        'title' => 'Open cases',
        'empty_message' => 'Open cases are not found by specified filter criteria',
    ],
    'settings' => [
        'crud' => [
        ],
        'paginate' => [
            'limit' => [
                'options' => [10, 25, 50],
            ],
        ],
    ],
    'columns' => [
        [
            'title' => 'Case Id',
            'formatter' => 'CaseLink',
            'name' => 'id',
        ],
        [
            'title' => 'Entity',
            'formatter' => 'CaseEntityNrStatus',
        ],
        [
            'title' => 'Traffic area',
            'formatter' => 'CaseTrafficArea',
        ],
        [
            'title' => 'Name',
            'formatter' => 'CaseEntityName',
        ],
        [
            'title' => 'Open Date',
            'formatter' => 'Date',
            'name' => 'openDate',
        ],
        [
            'title' => 'Type',
            'formatter' => 'RefData',
            'name' => 'caseType',
        ],
        [
            'title' => 'Category',
            'formatter' => 'RefData',
            'name' => 'categorys',
            'separator' => ', ',
        ],
        [
            'title' => 'Outcome',
            'formatter' => 'RefData',
            'name' => 'outcomes',
            'separator' => ', ',
        ],
    ],
];
