<?php

use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\NoteUrl;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'title' => 'Notes',
        'titleSingular' => 'Note',
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--multiple'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--multiple']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50, 100]
            ]
        ],
        'useQuery' => true
    ],
    'columns' => [
        [
            'title' => 'Created',
            'formatter' => NoteUrl::class,
        ],
        [
            'title' => 'Author',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Name::class;

                return $this->callFormatter($column, $data['createdBy']['contactDetails']['person']);
            }
        ],
        [
            'title' => 'Note',
            'formatter' => \Common\Service\Table\Formatter\Comment::class,
            'name' => 'comment',
        ],
        [
            'title' => 'Note type',
            'formatter' => function ($data) {

                /**
                 * @see https://jira.i-env.net/browse/OLCS-10256
                 */

                $description = \Common\Util\Escape::html($data['noteType']['description']);

                switch ($data['noteType']['id']) {
                    case 'note_t_lic':
                    case 'note_t_tm':
                    case 'note_t_org':
                        return $description;
                    case 'note_t_permit':
                        if (isset($data['irhpApplication']['id'])) {
                            $description .= ' ' . \Common\Util\Escape::html($data['irhpApplication']['id']);
                        }

                        return $description;
                    case 'note_t_app':
                        return $description . ' ' . \Common\Util\Escape::html($data['application']['id']);
                    case 'note_t_case':
                        return $description . ' ' . \Common\Util\Escape::html($data['case']['id']);
                }

                return 'BR ' . \Common\Util\Escape::html($data['busReg']['regNo']);
            }
        ],
        [
            'title' => 'Priority',
            'name' => 'priority',
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ],
    ]
];
