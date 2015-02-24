<?php
return array(
    'variables' => array(
        'action_route' => [
            'route' => 'submission_update_table',
            'params' => ['section' => 'prohibition-history']
        ],
        'title' => 'Prohibition history'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'prohibition-history',
            'actions' => array(
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'secondary', 'requireRows' => true)
            ),
            'action_field_name' => 'formAction'
        ),
        'submission_section' => 'display',
    ),
    'attributes' => array(
        'name' => 'prohibition-history'
    ),
    'columns' => array(
        array(
            'title' => 'Prohibition date',
            'formatter' => 'date',
            'name' => 'prohibitionDate'
        ),
        array(
            'title' => 'Date cleared',
            'name' => 'clearedDate',
            'formatter' => 'date',
        ),
        array(
            'title' => 'Vehicle',
            'name' => 'vehicle'
        ),
        array(
            'title' => 'Trailer',
            'name' => 'trailer'
        ),
        array(
            'title' => 'Imposed at',
            'name' => 'imposedAt'
        ),
        array(
            'title' => 'Type',
            'name' => 'prohibitionType'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}'
        ),
    )
);
