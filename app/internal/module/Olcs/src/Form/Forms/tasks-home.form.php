<?php

return array(
    'tasks-home' => array(
        'name' => 'tasks-home',
        'attributes' => array(
            'method' => 'get',
        ),
        'elements' => array(
            'assignedToTeam' => array(
                'type' => 'select-noempty',
                'label' => 'tasks.data.team',
            ),
            'assignedToUser' => array(
                'type' => 'select-noempty',
                'label' => 'tasks.data.owner',
            ),
            'category' => array(
                'type' => 'select-noempty',
                'label' => 'tasks.data.category',
            ),
            'taskSubCategory' => array(
                'type' => 'select-noempty',
                'label' => 'tasks.data.sub_category',
            ),
            'date' => array(
                'type' => 'select-noempty',
                'label' => 'tasks.data.date',
                'value_options' => 'task-date-types'
            ),
            'status' => array(
                'type' => 'select-noempty',
                'label' => 'tasks.data.status',
                'value_options' => 'task-status-types'
            ),
            'urgent' => array(
                'type' => 'checkbox',
                'label' => 'tasks.data.urgentOnly',
            ),
            'filter' => array(
                'type' => 'submit',
                'label' => 'tasks.submit.filter'
            ),
            'sort' => array(
                'type' => 'hidden',
            ),
            'order' => array(
                'type' => 'hidden',
            ),
            'limit' => array(
                'type' => 'hidden',
            )
        )
    )
);
