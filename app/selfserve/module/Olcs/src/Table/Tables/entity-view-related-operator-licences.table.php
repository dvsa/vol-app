<?php

use Common\Service\Table\Formatter\Date;
use Common\Service\Table\Formatter\RefData;
use Common\Service\Table\TableBuilder;

return [
    'variables' => [
        'empty_message' => 'entity-view-table-related-operator-licences.table.empty',
    ],
    'settings' => [],
    'attributes' => [],
    'columns' => [
        [
            'title' => 'search-result-label-lic-no',
            'formatter' => function ($data) {
                if (isset($data['id'])) {
                    /**
                     * @var TableBuilder $this
                     * @psalm-scope-this TableBuilder
                     */
                    return '<a href="' . $this->generateUrl(
                        ['entity' => 'licence', 'entityId' => $data['id']],
                        'entity-view',
                        false
                    ) . '" class="govuk-link">' . $data['licNo'] . '</a>';
                }
                return '';
            }
        ],
        [
            'title' => 'search-result-label-licence-status',
            'formatter' => RefData::class,
            'name' => 'status'
        ],
        [
            'title' => 'search-result-label-continuation-date',
            'formatter' => Date::class,
            'name' => 'expiryDate'
        ]
    ]
];
