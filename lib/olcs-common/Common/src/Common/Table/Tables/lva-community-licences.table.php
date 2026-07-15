<?php

use Common\Service\Table\Formatter\CommunityLicenceIssueNo;
use Common\Service\Table\Formatter\CommunityLicenceStatus;
use Common\Service\Table\Formatter\Date;

return [
    'variables' => [
        'title' => 'lva-community-licences-table-title',
        'within_form' => true,
        'empty_message' => 'lva-community-licences-table-empty-message'
    ],
    'settings' => [
        'crud' => [
            'actions' => [
                'add' => [
                    'label' => 'Add'
                ],
                'office-licence-add' => [
                    'label' => 'Add office licence'
                ],
                'annul' => [
                    'label' => 'Annul',
                    'requireRows' => true
                ],
                'restore' => [
                    'label' => 'Restore',
                    'requireRows' => true
                ],
                'stop' => [
                    'label' => 'Stop',
                    'requireRows' => true
                ],
                'reprint' => [
                    'label' => 'Reprint',
                    'requireRows' => true
                ]
            ]
        ],
        'row-disabled-callback' => static fn($row) => in_array(
            $row['status']['id'],
            [
                Common\RefData::COMMUNITY_LICENCE_STATUS_EXPIRED,
                Common\RefData::COMMUNITY_LICENCE_STATUS_VOID,
                Common\RefData::COMMUNITY_LICENCE_STATUS_RETURNDED
            ]
        ),
        'actionFormat' => Common\Service\Table\TableBuilder::ACTION_FORMAT_BUTTONS,
    ],
    'columns' => [
        [
            'title' => 'lva-community-licences-table-column-prefix',
            'name' => 'serialNoPrefix',
        ],
        [
            'title' => 'lva-community-licences-table-column-issue-date',
            'name' => 'specifiedDate',
            'formatter' => Date::class
        ],
        [
            'title' => 'lva-community-licences-table-column-issue-number',
            'name' => 'issueNo',
            'formatter' => CommunityLicenceIssueNo::class
        ],
        [
            'title' => 'lva-community-licences-table-column-status',
            'name' => 'status',
            'formatter' => CommunityLicenceStatus::class
        ],
        [
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
            'data-attributes' => ['status']
        ],
    ]
];
