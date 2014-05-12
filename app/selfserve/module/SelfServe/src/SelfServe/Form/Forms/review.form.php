<?php
return array(
    'review' => array(
        'name' => 'review',
        //'type' => 'read-only',
        'fieldsets' => array(
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
