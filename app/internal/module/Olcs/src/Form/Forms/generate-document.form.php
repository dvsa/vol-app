<?php

return [
    'generate-document' => [
        'name' => 'generate-document',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'details',
                'options' => [
                    'label' => 'documents.details'
                ],
                'elements' => [
                    'category' => [
                        'type' => 'select-noempty',
                        'label' => 'documents.data.category',
                    ],
                    'documentSubCategory' => [
                        'type' => 'select-noempty',
                        'label' => 'documents.data.sub_category'
                    ],
                    'documentTemplate' => [
                        'type' => 'select-noempty',
                        'label' => 'documents.data.template'
                    ]
                ]
            ],
            [
                'name' => 'bookmarks',
                'options' => [
                    'label' => 'documents.bookmarks'
                ],
                'elements' => [
                    /**
                     * We can't populate our bookmarks statically
                     * from config. They're one to many with the
                     * template the user chooses, and each bookmark
                     * has many child paragraphs. As such we have to
                     * build them up in the controller
                     */
                ]
            ],
            [
                'name' => 'form-actions',
                'attributes' => [
                    'class' => 'actions-container'
                ],
                'elements' => [
                    'submit' => [
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Generate',
                        'class' => 'action--primary large'
                    ],
                    'cancel' => [
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
