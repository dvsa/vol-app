<?php

return array(
    'application_payment-submission_summary' => array(
        'name' => 'application_payment-submission_summary',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
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
                        'label' => 'Take me to my dashboard',
                        'class' => 'action--primary large'
                    ),
                    'goToSummary' => array(
                        'enable' => true,
                        'type' => 'submit',
                        'filters' => '\Common\Form\Elements\InputFilters\ActionButton',
                        'label' => 'View my application summary',
                        'class' => 'action--secondary large'
                    )
                )
            )
        )
    )
);
