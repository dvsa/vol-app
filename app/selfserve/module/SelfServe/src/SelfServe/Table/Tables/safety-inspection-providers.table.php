<?php

$translationPrefix = 'safety-inspection-providers.table';

return array(
    'variables' => array(
        'title' => $translationPrefix . '.title',
        'empty_message' => $translationPrefix . '.empty_message',
        'required_label' => 'safety inspection provider',
        'within_form' => true
    ),
    'settings' => array(
        'crud' => array(
            'actions' => array(
                'add' => array('class' => 'primary'),
                'edit' => array('requireRows' => true),
                'delete' => array('class' => 'warning', 'requireRows' => true)
            )
        )
    ),
    'attributes' => array(
    ),
    'columns' => array(
        array(
            'width' => 'checkbox',
            'type' => 'Selector'
        ),
        array(
            'title' => $translationPrefix . '.providerName',
            'class' => 'action--tertiary',
            'action' => 'edit',
            'name' => 'fao',
            'type' => 'Action'
        ),
        array(
            'title' => $translationPrefix . '.external',
            'name' => 'isExternal',
            'formatter' => 'YesNo'
        ),
        array(
            'title' => $translationPrefix . '.address',
            'formatter' => 'Address'
        )
    )
);
