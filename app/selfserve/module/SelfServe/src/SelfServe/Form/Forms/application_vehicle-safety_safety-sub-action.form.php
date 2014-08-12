<?php

$translationPrefix = 'application_vehicle-safety_safety-sub-action';

return array(
    $translationPrefix => array(
        'name' => $translationPrefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' =>  array(
            array(
                'name' => 'data',
                'options' => array(0),
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'licence' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'isExternal' => array(
                        'type' => 'radio',
                        'label' => $translationPrefix . '.data.isExternal',
                        'value_options' => array(
                            'N' => $translationPrefix . '.data.isExternal.option.no',
                            'Y' => $translationPrefix . '.data.isExternal.option.yes'
                        )
                    )
                )
            ),
            array(
                'name' => 'contactDetails',
                'options' => array(),
                'elements' => array(
                    'fao' => array(
                        'type' => 'text',
                        'label' => $translationPrefix . '.data.fao',
                        'filters' => '\Common\Form\Elements\InputFilters\TextRequired'
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    )
                )
            ),
            array(
                'name' => 'address',
                'options' => array(
                    'label' => $translationPrefix . '.address.label'
                ),
                'type' => 'address'
            ),
            array(
                'type' => 'journey-crud-buttons'
            )
        )
    )
);
