<?php


return [
    'condition-undertaking-form' => [
        'name' => 'condition-undertaking-form',
        'attributes' => [
            'method' => 'post',
        ],
        'fieldsets' => [
            [
                'name' => 'condition-undertaking',
                'elements' => [
                    'id' => [
                        'type' => 'hidden'
                    ],
                    'version' => [
                        'type' => 'hidden'
                    ],
                    'vosaCase' => [
                        'type' => 'hidden'
                    ],
                    'licence' => [
                        'type' => 'hidden'
                    ],
                    'conditionType' => [
                        'type' => 'hidden'
                    ],
                    'addedVia' => [
                        'type' => 'hidden',
                        'value' => 'Case'
                    ],
                    'isDraft' => [
                        'type' => 'hidden',
                        'value' => 0,
                    ],
                    'notes' => [
                        'type' => 'textarea',
                        'filters' => '\Common\Form\Elements\InputFilters\TextMax8000Required',
                        'class' => 'extra-long',

                    ],
                    'isFulfilled' => [
                        'type' => 'checkbox-boolean',
                        'label' => 'Fulfilled',
                    ],
                    'attachedTo' => [
                        'type' => 'select-group',
                        'label' => 'Attached to'
                    ],
               ],
           ],
            array(
                'name' => 'form-actions',
                'attributes' => array(
                    'class' => 'actions-container'
                ),
                'options' => array(0),
                'elements' => array(
                    'submit' => array(
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Save',
                        'class' => 'action--primary large'
                    ),
                    'cancel' => array(
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'Cancel',
                        'class' => 'action--secondary large'
                    )
                )
            ),
        ],
    ]
];
