<?php

return array(
    'application_previous-history_licence-history' => array(
        'name' => 'application_previous-history_licence-history',
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'data',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'currentLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_currentLicence'
                    ),
                    'appliedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_appliedLicence'
                    ),
                    'refusedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_refusedLicence'
                    ),
                    'revokedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_revokedLicence'
                    ),
                    'publicInquiryLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_publicInquiryLicence'
                    ),
                    'disqualifiedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_disqualifiedLicence'
                    ),
                    'heldLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => 'application_previous-history_licence-history_heldLicence'
                    ),
                )
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
