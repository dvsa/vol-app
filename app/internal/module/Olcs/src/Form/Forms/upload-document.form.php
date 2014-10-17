<?php

return [
    'upload-document' => [
        'name' => 'upload-document',
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
                    'description' => [
                        'type' => 'text',
                        'label' => 'documents.data.description'
                    ],
                    'file' => [
                        'type' => 'file',
                        'label' => 'documents.data.file'
                    ]
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
                        'label' => 'Upload',
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
