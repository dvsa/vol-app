<?php

return [
    'finalise-document' => [
        'name' => 'finalise-document',
        'attributes' => [
            'method' => 'post',
        ],
        'elements' => [
            'category' => [
                'type' => 'plainText',
                'label' => 'documents.data.category'
            ],
            'subCategory' => [
                'type' => 'plainText',
                'label' => 'documents.data.subCategory'
            ],
            'template' => [
                'type' => 'plainText',
                'label' => 'documents.data.template'
            ],
            'link' => [
                'type' => 'html',
                'label' => 'documents.data.link'
            ],
            'file' => [
                'type' => 'file',
                'label' => 'documents.data.fileUpload'
            ],
            'submit' => [
                'type' => 'submit',
                'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                'label' => 'Save',
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
];
