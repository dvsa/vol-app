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
                'elements' => []
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
