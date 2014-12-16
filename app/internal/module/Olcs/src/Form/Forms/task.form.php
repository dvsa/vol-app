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
                'elements' => [
                    'link' => [
                        'type' => 'html',
                        'label' => 'tasks.data.link',
                    ],
                    'status' => [
                        'type' => 'html',
                        'label' => 'tasks.data.status',
                    ],

                    'actionDate' => [
                        'type' => 'dateSelectAllowFuture',
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
                    'subCategory' => [
                        'type' => 'select-noempty',
                        'label' => 'tasks.data.sub_category'
                    ],
                    'description' => [
                        'type' => 'text',
                        'label' => 'tasks.data.description',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax255Required',
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
                        'filters' => '\Common\Form\Elements\InputFilters\SelectEmpty'
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
                    'close' => [
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Close',
                        'class' => 'action--secondary large'
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
            'linkType' => [
                'type' => 'hidden'
            ],
            'linkId' => [
                'type' => 'hidden'
            ],
        ]
    ]
];
