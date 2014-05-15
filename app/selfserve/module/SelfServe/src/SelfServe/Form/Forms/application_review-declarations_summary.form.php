<?php

return array(
    'application_review-declarations_summary' => array(
        'name' => 'application_review-declarations_summary',
        'disabled' => true,
        'attributes' => array(
            'class' => 'read-only'
        ),
        'fieldsets' => array(
            array(
                'name' => 'title',
                'elements' => array(
                    'title' => array(
                        'type' => 'hidden',
                        'label' => '<h2>1. Type of licence</h2>',
                    )
                )
            ),
            array(
                'type' => 'operator-location'
            ),
            array(
                'type' => 'operator-type',
                'options' => array(
                    'label' => ''
                )
            ),
            array(
                'type' => 'licence-type'
            ),
            array(
                'type' => 'licence-type-psv'
            )
        ),
        array(
            'type' => 'journey-buttons'
        )
    )
);
