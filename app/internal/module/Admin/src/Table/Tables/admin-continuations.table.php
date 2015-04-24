<?php

return array(
    'variables' => array(

    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'print-letters' => array(
                    'value' => 'Print letters',
                    'class' => 'primary',
                    'requireRows' => true
                ),
                'print-page' => array(
                    'value' => 'Print page',
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
            'stack' => ['status', 'description'],
            'formatter' => 'StackValue'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'type' => 'Checkbox'
        )
    )
);
