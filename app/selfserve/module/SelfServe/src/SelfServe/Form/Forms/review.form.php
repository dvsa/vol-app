<?php
return array(
    'review' => array(
        'name' => 'review',
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
                ),
            ),
            array(
                'type' => 'operator-location',
            ),
            array(
                'type' => 'operator-type',
                'options' => array(
                    // we want to suppress the default label within
                    // this particular form. A blank label won't render
                    // an empty <legend> element, don't worry
                    'label' => '',
                ),
            ),
            array(
                'type' => 'licence-type',
            ),
        ),
    ),
);
