<?php

use Common\Service\Table\Formatter\Date;
use Olcs\Module;

return [
    'variables' => [
        'titleSingular' => 'Publication',
        'title' => 'Publications'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ]
    ],
    'columns' => [
        [
            'title' => 'Created',
            'formatter' => function ($data, $column) {
                $column['formatter'] = Date::class;
                $lva = $column['lva'] ?? 'licence';
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    $lva .'/processing/publications',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'createdOn',
            'sort' => 'createdOn'
        ],
        [
            'title' => 'Publication No.',
            'isNumeric' => true,
            'formatter' => fn($data) => $data['publication']['publicationNo']
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data) => $data['publication']['pubType']
        ],
        [
            'title' => 'Traffic area',
            'formatter' => fn($data) => $data['publication']['trafficArea']['name']
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data) => $data['publication']['pubStatus']['description']
        ],
        [
            'title' => 'Publication date',
            'formatter' => function ($data) {
                $date = new DateTime($data['publication']['pubDate']);
                return $date->format(Module::$dateFormat);
            }
        ],
        [
            'title' => 'Section',
            'formatter' => fn($data) => $data['publicationSection']['description']
        ],
        [
            'title' => 'Text',
            'formatter' => function ($data) {
                $string = nl2br($data['text1']) . '<br />' . $data['text2'];
                if (strlen($string) > 100) {
                    return substr($string, 0, 100) . ' [...]';
                }

                return $string;
            }
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
