<?php

use \Common\Service\Entity\ContinuationDetailEntityService;

return array(
    'variables' => array(

    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'print-letters' => array(
                    'label' => 'Print letters',
                    'class' => 'primary',
                    'requireRows' => true
                ),
                'print-page' => array(
                    'label' => 'Print page',
                    'class' => 'secondary',
                    'requireRows' => true
                ),
            )
        )
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
            'formatter' => function ($data) {
                $content = $data['status']['description'];

                if ($data['status']['id'] === ContinuationDetailEntityService::STATUS_COMPLETE
                    && $data['received'] === 'N') {
                    $content .= ' (not received)';
                }

                return $content;
            }
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
