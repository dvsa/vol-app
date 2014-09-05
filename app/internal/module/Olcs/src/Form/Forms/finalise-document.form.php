<?php

return [
    'finalise-document' => [
        'name' => 'finalise-document',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
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
            'category' => [
                'type' => 'plainText',
                'label' => 'documents.data.category'
            ],
            'subCategory' => [
                'type' => 'plainText',
                'label' => 'documents.data.sub_category'
            ],
            'template' => [
                'type' => 'html',
                'label' => 'documents.data.template'
            ],
            'file' => [
                'type' => 'file',
                'label' => 'documents.data.file'
            ]
        ]
    ]
];
