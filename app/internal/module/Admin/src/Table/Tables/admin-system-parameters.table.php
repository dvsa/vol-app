<?php

return array(
    'variables' => array(
        'title' => 'parameters',
        'titleSingular' => 'parameter'
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
    'columns' => array(
        array(
            'title' => 'Key',
            'name' => 'id',
            'sort' => 'id',
            'formatter' => 'SystemParameterLink'
        ),
        array(
            'title' => 'Value',
            'name' => 'paramValue',
            'sort' => 'paramValue',
        ),
        array(
            'title' => 'Description',
            'name' => 'description',
            'sort' => 'description'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
