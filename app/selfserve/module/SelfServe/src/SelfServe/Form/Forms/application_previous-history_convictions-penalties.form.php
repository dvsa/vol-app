<?php

return array(
    'application_previous-history_convictions-penalties' => array(
        'name' => 'application_previous-history_convictions-penalties',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'prevConviction' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'selfserve-app-subSection-previous-history-criminal-conviction-hasConv',
                    ),
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                )
            ),
            array(
                'name' => 'table',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'convictionsConfirmation',
                'elements' => array(
                    'convictionsConfirmation' => array(
                        'type' => 'multicheckbox',
                        'label' => 'Confirm',
                        'value_options' => array(
                            '1' => 'selfserve-app-subSection-previous-history-criminal-conviction-labelConfirm'
                        ),
                    ),
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
