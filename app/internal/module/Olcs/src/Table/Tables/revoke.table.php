<?php
return array(
    'variables' => array(
        'title' => 'In office revocation'
    ),
    'settings' => array(
        'crud' => array(
            'formName' => 'revoke',
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'secondary', 'requireRows' => true)
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(

        array(
            'title' => 'Legislation',
            'name' => 'legislation'
        ),
        array(
            'title' => 'TC/TD agreed',
            'name' => 'presidingTc'
        ),
        array(
            'title' => 'Agreed date',
            'name' => 'ptrAgreedDate'
        ),
        array(
            'title' => 'Closed date',
            'name' => 'closedDate'
        ),
        array(
            'title' => 'Comment',
            'name' => 'comment',
            'formatter' => function ($data, $column) {
                    return substr($data['notes'], 0, 150);
                },
        )
    )
);
