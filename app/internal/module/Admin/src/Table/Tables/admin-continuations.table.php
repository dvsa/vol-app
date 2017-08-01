<?php

use Common\Service\Entity\ContinuationDetailEntityService;
use Common\Service\Entity\LicenceEntityService;

return array(
    'variables' => array(
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'generate' => array(
                    'label' => 'Generate',
                    'class' => 'action--primary js-require--multiple',
                    'requireRows' => true
                ),
            )
        ),
        'row-disabled-callback' => function ($row) {
            $enabledLicenceStatuses = [
                LicenceEntityService::LICENCE_STATUS_VALID,
                LicenceEntityService::LICENCE_STATUS_CURTAILED,
                LicenceEntityService::LICENCE_STATUS_SUSPENDED
            ];

            $enabledStatuses = [
                ContinuationDetailEntityService::STATUS_PREPARED,
                ContinuationDetailEntityService::STATUS_PRINTING,
                ContinuationDetailEntityService::STATUS_PRINTED,
                ContinuationDetailEntityService::STATUS_ERROR
            ];

            return !(
                in_array($row['licence']['status']['id'], $enabledLicenceStatuses)
                && in_array($row['status']['id'], $enabledStatuses)
            );
        }
    ),
    'columns' => array(
        array(
            'title' => 'Operator name',
            'stack' => ['licence', 'organisation', 'name'],
            'formatter' => 'StackValue'
        ),
        array(
            'title' => 'Licence',
            'stringFormat' => '<a href="[LINK]">{licence->licNo}</a> ({licence->status->description})',
            'formatter' => 'StackValueReplacer',
            'type' => 'Link',
            'route' => 'lva-licence',
            'params' => [
                'licence' => '{licence->id}'
            ]
        ),
        array(
            'title' => 'Licence type',
            'formatter' => 'LicenceTypeShort'
        ),
        array(
            'title' => 'Method',
            'formatter' => function ($data) {
                return ($data['licence']['organisation']['allowEmail'] === 'Y' ? 'Email' : 'Post');
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => 'RefData',
            'name' => 'status'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
        )
    )
);
