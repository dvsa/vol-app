<?php

return [
    'task' => [
        'name' => 'task',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'details',
                'options' => [
                    'label' => 'tasks.details'
                ],
                'elements' => [
                    /*
                     * link =>
                     *
                     * status =>
                     */
                    'actionDate' => [
                        'type' => 'dateSelect',
                        'label' => 'tasks.data.actionDate',
                    ],
                    'urgent' => [
                        'type' => 'checkbox',
                        'label' => 'tasks.data.urgent',
                    ],
                    'category' => [
                        'type' => 'select-noempty',
                        'label' => 'tasks.data.category',
                    ],
                    'taskSubCategory' => [
                        'type' => 'select-noempty',
                        'label' => 'tasks.data.sub_category',
                    ],

                ]
            ],
            [
                'name' => 'assignment',
                'options' => [
                    'label' => 'tasks.assignment'
                ],
                'elements' => [
                    'assignedToTeam' => [
                        'type' => 'select-noempty',
                        'label' => 'tasks.data.team',
                    ],
                    'assignedToUser' => [
                        'type' => 'select-noempty',
                        'label' => 'tasks.data.owner',
                    ],
                ]
            ],
            [
                'name' => 'form-actions',
                'attributes' => [
                    'class' => 'actions-container'
                ],
                'elements' => [
                    'submit' => [
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Save',
                        'class' => 'action--primary large'
                    ],
                    'cancel' => [
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    ]
                ]
            ]

        ],
        'elements' => [
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
        ]
    ]
];
