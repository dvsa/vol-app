<?php

return array(
    'variables' => array(
        'title' => 'Alpha splits',
        'within_form' => true,
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'addAlphasplit' => array('class' => 'action--primary', 'label' => 'add'),
                'editAlphasplit' => array(
                    'class' => 'action--secondary js-require--one',
                    'label' => 'edit',
                    'requireRows' => true
                ),
                'deleteAlphasplit' => array(
                    'class' => 'action--secondary js-require--multiple',
                    'label' => 'delete',
                    'requireRows' => true
                )
            )
        ),
        // This has to exist so that the title gets prepended with the document count
        'paginate' => array(
        )
    ),

    'columns' => array(
        array(
            'title' => 'User',
            'name' => 'user->contactDetails->person',
            'formatter' => 'Name',
        ),
        array(
            'title' => 'Assign operator tasks starting with these letters',
            'formatter' => function ($data) {
                return $data['letters'];
            }
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        ),
    )
);
