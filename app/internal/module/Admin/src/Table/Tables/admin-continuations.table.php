<?php

use Common\RefData;
use Common\Service\Table\Formatter\LicenceTypeShort;
use Common\Service\Table\Formatter\StackValue;
use Common\Service\Table\Formatter\StackValueReplacer;

return array(
    'variables' => array(
        'title' => 'Continuations',
        'titleSingular' => 'Continuation',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'generate' => array(
                    'label' => 'Generate',
                    'class' => 'govuk-button js-require--multiple',
                    'requireRows' => true
                ),
            )
        ),
        'row-disabled-callback' => function ($row) {
            $enabledLicenceStatuses = [
                RefData::LICENCE_STATUS_VALID,
                RefData::LICENCE_STATUS_CURTAILED,
                RefData::LICENCE_STATUS_SUSPENDED
            ];

            $enabledStatuses = [
                RefData::CONTINUATION_DETAIL_STATUS_PREPARED,
                RefData::CONTINUATION_DETAIL_STATUS_PRINTING,
                RefData::CONTINUATION_DETAIL_STATUS_PRINTED,
                RefData::CONTINUATION_DETAIL_STATUS_ERROR
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
            'formatter' => StackValue::class
        ),
        array(
            'title' => 'Licence',
            'stringFormat' => '<a class="govuk-link" href="[LINK]">{licence->licNo}</a> ({licence->status->description})',
            'formatter' => StackValueReplacer::class,
            'type' => 'Link',
            'route' => 'lva-licence',
            'params' => [
                'licence' => '{licence->id}'
            ]
        ),
        array(
            'title' => 'Licence type',
            'formatter' => LicenceTypeShort::class
        ),
        array(
            'title' => 'Method',
            'formatter' => function ($data) {
                return ($data['licence']['organisation']['allowEmail'] === 'Y' ? 'Email' : 'Post');
            }
        ),
        array(
            'title' => 'Status',
            'formatter' => \Common\Service\Table\Formatter\RefData::class,
            'name' => 'status'
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
        )
    )
);
