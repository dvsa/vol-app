<?php

return array(
    'tasks-home' => array(
        'name' => 'tasks-home',
        'attributes' => array(
            'method' => 'get',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'team' => array(
                        'type' => 'select',
                        'label' => 'Team',
                    ),
                    'owner' => array(
                        'type' => 'select',
                        'label' => 'Owner',
                    ),
                    'category' => array(
                        'type' => 'select',
                        'label' => 'Category',
                    ),
                    'sub_category' => array(
                        'type' => 'select',
                        'label' => 'Sub category',
                    ),
                    'date' => array(
                        'type' => 'select',
                        'label' => 'Date',
                    ),
                    'urgent' => array(
                        'type' => 'checkbox',
                        'label' => 'Urgent only',
                    )
                )
            )
        )
    )
);
