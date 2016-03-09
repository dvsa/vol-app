<?php

return array(
    'variables' => array(
        'title' => 'Serious Infringements'
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'edit' => array('requireRows' => true, 'class' => 'secondary js-require--one'),
                'send' => array('requireRows' => true, 'class' => 'primary', 'label' => 'Send MSI response')
            )
        ),
        'paginate' => array(
            'limit' => array(
                'default' => 10,
                'options' => array(10, 25, 50, 100)
            )
        )
    ),
    'columns' => array(
        array(
            'title' => 'ID',
            'name' => 'id',
            'formatter' => 'SeriousInfringementLink'
        ),
        array(
            'title' => 'Opposition type',
            'formatter' => 'RefData',
            'name' => 'siCategoryType'
        ),
        array(
            'title' => 'Response set',
            'formatter' => 'YesNo',
            'name' => 'responseSet'
        ),
        array(
            'title' => '',
            'width' => 'checkbox',
            'format' => '{{[elements/radio]}}'
        )
    )
);
