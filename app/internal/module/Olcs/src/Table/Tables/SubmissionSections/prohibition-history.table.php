<?php
return array(
    'variables' => array(
        'id' => 'prohibition-history',
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
                'refresh-table' => array('label' => 'Refresh table', 'class' => 'action--secondary', 'requireRows' => false),
                'delete-row' => array('label' => 'Delete row', 'class' => 'action--secondary', 'requireRows' => true)
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
            'name' => 'prohibitionDate'
        ),
        array(
            'title' => 'Date cleared',
            'name' => 'clearedDate'
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
            'type' => 'Checkbox',
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/checkbox]}}',
            'hideWhenDisabled' => true
        ),
    )
);
