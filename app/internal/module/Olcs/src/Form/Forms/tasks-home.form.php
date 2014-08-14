<?php

return array(
    'tasks-home' => array(
        'name' => 'tasks-home',
        'attributes' => array(
            'method' => 'get',
        ),
        'elements' => array(
            'team' => array(
                'type' => 'select-noempty',
                'label' => 'tasks-home.data.team',
            ),
            'owner' => array(
                'type' => 'select-noempty',
                'label' => 'tasks-home.data.owner',
            ),
            'category' => array(
                'type' => 'select-noempty',
                'label' => 'tasks-home.data.category',
            ),
            'subCategory' => array(
                'type' => 'select-noempty',
                'label' => 'tasks-home.data.sub_category',
            ),
            'date' => array(
                'type' => 'select-noempty',
                'label' => 'tasks-home.data.date',
                'value_options' => 'task-date-types'
            ),
            'status' => array(
                'type' => 'select-noempty',
                'label' => 'tasks-home.data.status',
                'value_options' => 'task-status-types'
            ),
            'urgent' => array(
                'type' => 'checkbox',
                'label' => 'tasks-home.data.urgent',
            ),
            'filter' => array(
                'type' => 'submit',
                'label' => 'tasks-home.submit.filter'
            ),
            'sort' => array(
                'type' => 'hidden',
            ),
            'order' => array(
                'type' => 'hidden',
            )
        )
    )
);
