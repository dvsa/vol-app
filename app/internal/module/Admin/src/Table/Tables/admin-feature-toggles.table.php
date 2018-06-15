<?php

return array(
    'variables' => array(
        'title' => 'Feature toggles'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary', 'requireRows' => false),
                'edit' => array('requireRows' => true, 'class' => 'action--secondary js-require--one'),
                'delete' => array('requireRows' => true, 'class' => 'action--secondary js-require--one')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            ),
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Name',
            'name' => 'friendlyName',
            'sort' => 'friendlyName',
            'formatter' => 'featureToggleEditLink'
        ),
        array(
            'title' => 'Handler (or config key)',
            'name' => 'configName',
            'sort' => 'configName'
        ),
        array(
            'title' => 'Status',
            'name' => 'status',
            'sort' => 'status',
            'formatter' => 'refDataStatus'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
