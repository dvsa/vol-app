<?php

$translationPrefix = 'application_taxi-phv_licence-sub-action';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'options' => array(
                    'label' => $translationPrefix . '.data',
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'privateHireLicenceNo' => array(
                        'type' => 'text-required',
                        'label' => $translationPrefix . '.data.licNo'
                    ),
                    'licence' => array(
                        'type' => 'hidden'
                    )
                )
            ),
            array(
                'name' => 'contactDetails',
                'options' => array(
                    'label' => $translationPrefix . '.contactDetails',
                ),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'description' => array(
                        'type' => 'text-required',
                        'label' => $translationPrefix . '.contactDetails.description'
                    ),
                )
            ),
            array(
                'type' => 'address',
                'options' => array(
                )
            ),
            array(
                'type' => 'journey-crud-buttons'
            )
        )
    )
);
