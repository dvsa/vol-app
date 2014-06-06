<?php


return [
    'pi-sla' => [
        'name' => 'Public inquiry SLA',
        'attributes' => [
            'method' => 'post',
        ],
        'type' => 'Common\Form\Form',
        'elements' => [
            'agreedDate' => [
                'type' => 'dateSelect',
                'label' => 'Agreed date',
                'value_options' => 'defendant_types'
            ],
            'piDate' => [
                'type' => 'dateSelect',
                'label' => 'Date of PI',
                 'class' => 'long'
            ],
            'decisionDate' => [
                'type' => 'dateSelect',
                'label' => 'Date of decision',
                 'class' => 'long'
            ],
            'vosaCase' => [
                'type' => 'hidden'
            ],
            'id' => [
                'type' => 'hidden'
            ],
            'version' => [
                'type' => 'hidden'
            ],
            /*'save-add' => [
                'type' => 'submit',
                'label' => 'Save & add another',
                'class' => 'action--primary large'
            ],*/
            'conviction' => [
                'type' => 'submit',
                'label' => 'Save',
                'class' => 'action--primary large'
            ],
            'cancel' => [
                'name' => 'cancel-conviction',
                'type' => 'submit',
                'label' => 'Cancel',
                'class' => 'action--secondary large'
            ]
        ]
    ]
];
