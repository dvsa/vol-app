<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\TableBuilder;
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
            'title' => 'Created date',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Date::class;
                return '<a href="' . $this->generateUrl(
                    ['action' => 'edit', 'id' => $data['id']],
                    'transport-manager/processing/publication',
                    true
                ) . '" class="govuk-link js-modal-ajax">' . $this->callFormatter($column, $data) . '</a>';
            },
            'name' => 'createdOn',
            'sort' => 'createdOn'
        ],
        [
            'title' => 'Publication No.',
            'isNumeric' => true,
            'formatter' => fn($data) => \Common\Util\Escape::html($data['publication']['publicationNo'])
        ],
        [
            'title' => 'Type',
            'formatter' => fn($data) => \Common\Util\Escape::html($data['publication']['pubType'])
        ],
        [
            'title' => 'Traffic area',
            'formatter' => fn($data) => \Common\Util\Escape::html($data['publication']['trafficArea']['name'])
        ],
        [
            'title' => 'Status',
            'formatter' => fn($data) => \Common\Util\Escape::html($data['publication']['pubStatus']['description'])
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
            'formatter' => fn($data) => \Common\Util\Escape::html($data['publicationSection']['description'])
        ],
        [
            'title' => 'Text',
            'formatter' => function ($data) {
                // Escaped before nl2br, so the <br /> it inserts stays markup while the text
                // around it cannot be. Truncation below can split an entity, which renders as
                // literal text — the same cosmetic risk the original had with the <br /> tag.
                $string = nl2br(\Common\Util\Escape::html((string) $data['text1']))
                    . '<br />' . \Common\Util\Escape::html((string) $data['text2']);
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
