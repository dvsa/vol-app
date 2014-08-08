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
                        'label' => 'tasks-home.data.team',
                    ),
                    'owner' => array(
                        'type' => 'select',
                        'label' => 'tasks-home.data.owner',
                    ),
                    'category' => array(
                        'type' => 'select',
                        'label' => 'tasks-home.data.category',
                    ),
                    'sub_category' => array(
                        'type' => 'select',
                        'label' => 'tasks-home.data.sub_category',
                    ),
                    'date' => array(
                        'type' => 'select',
                        'label' => 'tasks-home.data.date',
                    ),
                    'urgent' => array(
                        'type' => 'checkbox',
                        'label' => 'tasks-home.data.urgent',
                    )
                )
            )
        )
    )
);
