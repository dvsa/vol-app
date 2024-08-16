<?php

use Common\Service\Table\Formatter\CommunityLicenceIssueNo;
use Common\Service\Table\Formatter\CommunityLicenceStatus;
use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'titleSingular' => 'Community Licence',
        'title' => 'Community Licences',
        'within_form' => true,
    ],
    'settings' => [
        'paginate' => [
            'limit' => [
                'default' => 50,
                'options' => [50]
            ]
        ],
        'crud' => [
            'actions' => [
                'add' => ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Add'],
                'office-licence-add' => ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Add office licence'],
                'restore' => ['class' => 'govuk-button govuk-button--secondary', 'value' => 'Restore']
            ]
        ],
        'row-disabled-callback' => fn($row) => in_array(
            $row['status']['id'],
            [
                Common\RefData::COMMUNITY_LICENCE_STATUS_EXPIRED,
                Common\RefData::COMMUNITY_LICENCE_STATUS_VOID,
                Common\RefData::COMMUNITY_LICENCE_STATUS_RETURNDED
            ]
        ),
    ],
    'attributes' => [
    ],
    'columns' => [
        [
            'title' => 'Prefix',
            'sort' => 'prefix',
            'name' => 'serialNoPrefix',
        ],
        [
            'title' => 'Date Issued',
            'formatter' => Date::class,
            'sort' => 'specifiedDate',
            'name' => 'specifiedDate',
        ],
        [
            'title' => 'Issue number',
            'sort' => 'issueNo',
            'name' => 'issueNo',
            'formatter' => CommunityLicenceIssueNo::class,
        ],
        [
            'title' => 'Status',
            'name' => 'status',
            'formatter' => CommunityLicenceStatus::class
        ],
        [
            'type' => 'Checkbox',
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'disableIfRowIsDisabled' => true,
            'data-attributes' => ['status']
        ],
    ]
];
