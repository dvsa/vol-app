<?php

return array(
    'variables' => array(
        'titleSingular' => 'Recipient',
        'title' => 'Recipients'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'action--primary', 'requireRows' => false),
                'edit' => array('class' => 'action--secondary js-require--one', 'requireRows' => true),
                'delete' => array('class' => 'action--secondary js-require--one', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 25,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'title' => 'Contact Name',
            'name' => 'contactName',
            'sort' => 'contactName',
        ),
        array(
            'title' => 'Email',
            'name' => 'emailAddress',
            'sort' => 'emailAddress',
        ),
        array(
            'title' => 'markup-table-th-action', //this is a view partial from olcs-common
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
