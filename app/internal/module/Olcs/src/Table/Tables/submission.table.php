<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\HideIfClosedRadio;
use Common\Service\Table\Formatter\Name;
use Common\Service\Table\Formatter\YesNo;
use Common\Service\Table\TableBuilder;
use Olcs\Module;

return [
    'variables' => [
        'titleSingular' => 'Submission',
        'title' => 'Submissions'
    ],
    'settings' => [
        'crud' => [
            'formName' => 'submission',
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['requireRows' => true, 'class' => 'govuk-button govuk-button--warning js-require--one']
            ]
        ],
        'paginate' => [
            'limit' => [
                'default' => 10,
                'options' => [10, 25, 50]
            ]
        ],
        'useQuery' => true
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'formatter' => HideIfClosedRadio::class
        ],
        [
            'title' => 'Submission No.',
            'formatter' => fn($row) =>
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                '<a class="govuk-link" href="' . $this->generateUrl(
                    ['submission' => $row['id'], 'action' => 'details'],
                    'submission',
                    true
                ) . '">' . $row['id'] . '</a>',
            'sort' => 'id'
        ],
        [
            'title' => 'Type',
            'formatter' => fn($row) => $row['submissionType']['description'],
            'name' => 'submissionType',
        ],
        [
            'title' => 'Sub status',
            'formatter' => fn($row) => !empty($row['closedDate']) ? 'Closed' : 'Open',
        ],
        [
            'title' => 'Date created',
            'formatter' => fn($row) => date(Module::$dateTimeSecFormat, strtotime($row['createdOn'])),
            'sort' => 'createdOn'
        ],
        [
            'title' => 'Date closed',
            'formatter' => Date::class,
            'name' => 'closedDate'
        ],
        [
            'title' => 'Currently with',
            'formatter' => function ($data, $column) {
                /**
                 * @var TableBuilder $this
                 * @psalm-scope-this TableBuilder
                 */
                $column['formatter'] = Name::class;
                if (!empty($data['recipientUser']['contactDetails']['person'])) {
                    return $this->callFormatter($column, $data['recipientUser']['contactDetails']['person']);
                }
                if (!empty($data['createdBy']['contactDetails']['person'])) {
                    return $this->callFormatter($column, $data['createdBy']['contactDetails']['person']);
                }
                return '';
            }
        ],
        [
            'title' => 'Date assigned',
            'formatter' => \Common\Service\Table\Formatter\DateTime::class,
            'name' => 'assignedDate'
        ],
        [
            'title' => 'Urgent',
            'formatter' => YesNo::class,
            'name' => 'urgent'
        ]
    ]
];
