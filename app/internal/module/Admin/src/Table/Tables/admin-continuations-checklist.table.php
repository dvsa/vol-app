<?php

return array(
    'variables' => array(
        'title' => 'Continuations',
        'titleSingular' => 'Continuation',
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'generate-letters' => array(
                    'label' => 'Generate letters',
                    'class' => 'govuk-button js-require--multiple',
                    'requireRows' => true
                ),
                'export' => array(
                    'label' => 'Export',
                    'class' => 'govuk-button govuk-button--secondary js-disable-crud js-require--multiple',
                    'requireRows' => true
                ),
            )
        ),
    ),
    'columns' => array(
        array(
            'title' => 'Operator name',
            'stack' => ['licence', 'organisation', 'name'],
            'formatter' => 'StackValue'
        ),
        array(
            'title' => 'Licence',
            'stringFormat' => '<a class="govuk-link" href="[LINK]">{licence->licNo}</a> ({licence->status->description})',
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
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'type' => 'Checkbox',
            'disableIfRowIsDisabled' => true,
        )
    )
);
