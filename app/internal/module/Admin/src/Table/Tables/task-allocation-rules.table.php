<?php

use Common\Service\Table\Formatter\TaskAllocationCriteria;
use Common\Service\Table\Formatter\TaskAllocationUser;

return [
    'variables' => [
        'titleSingular' => 'allocation rule',
        'title' => 'allocation rules'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button'],
                'edit' => ['class' => 'govuk-button govuk-button--secondary js-require--one'],
                'delete' => ['class' => 'govuk-button govuk-button--warning js-require--multiple']
            ]
        ],
        // This has to exist so that the title gets prepended with the document count
        'paginate' => [
        ]
    ],

    'columns' => [
        [
            'title' => 'Category',
            'formatter' => function ($row) {
                $url = $this->generateUrl(
                    ['id' => $row['id'], 'action' => 'edit'],
                    'admin-dashboard/task-allocation-rules'
                );
                return '<a class="govuk-link" href="'. $url . '">' . $row['category']['description'] .'</a>';
            }
        ],
        [
            'title' => 'Sub Category',
            'formatter' => fn($row) => $row['subCategory']['subCategoryName'] ?? 'N/A'
        ],
        [
            'title' => 'Criteria',
            'formatter' => TaskAllocationCriteria::class,
        ],
        [
            'title' => 'Traffic Area',
            'formatter' => function ($row) {
                if (empty($row['trafficArea']['name'])) {
                    return 'N/A';
                }
                return $row['trafficArea']['name'];
            }
        ],
        [
            'title' => 'Team',
            'formatter' => fn($data) => $data['team']['name']
        ],
        [
            'title' => 'User',
            'name' => 'user->contactDetails->person',
            'formatter' => TaskAllocationUser::class,
        ],
        [
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox'
        ],
    ]
];
