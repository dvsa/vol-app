<?php
$prefix = 'application_previous-history_licence-history';

return array(
    $prefix => array(
        'name' => $prefix,
        'attributes' => array(
            'method' => 'post',
        ),
        'fieldsets' => array(
            array(
                'name' => 'dataLicencesCurrent',
                'elements' => array(
                    'id' => array(
                        'type' => 'hidden'
                    ),
                    'version' => array(
                        'type' => 'hidden'
                    ),
                    'currentLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_currentLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                ),
                'options' => array(
                    'label' => $prefix . '.title'
                ),
            ),
            array(
                'name' => 'table-licences-current',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataLicencesApplied',
                'elements' => array(
                    'personsInformation' => array(
                        'type' => 'plainText',
                        'label' => $prefix . '_personsInformation'
                    ),
                    'appliedForLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_appliedForLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                ),
            ),
            array(
                'name' => 'table-licences-applied',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataLicencesRefused',
                'elements' => array(
                    'refusedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_refusedLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                ),
            ),
            array(
                'name' => 'table-licences-refused',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataLicencesRevoked',
                'elements' => array(
                    'revokedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_revokedLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                ),
            ),
            array(
                'name' => 'table-licences-revoked',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataLicencesPublicInquiry',
                'elements' => array(
                    'publicInquiryLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_publicInquiryLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                ),
            ),
            array(
                'name' => 'table-licences-public-inquiry',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataLicencesDisqualified',
                'elements' => array(
                    'disqualifiedLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_disqualifiedLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                ),
            ),
            array(
                'name' => 'table-licences-disqualified',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'name' => 'dataLicencesHeld',
                'elements' => array(
                    'heldLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_heldLicence',
                        'filters' => '\Common\Form\Elements\InputFilters\PreviousHistoryLicenceHistoryNeedLicence'
                    ),
                )
            ),
            array(
                'name' => 'table-licences-held',
                'options' => array(0),
                'type' => 'table'
            ),
            array(
                'type' => 'journey-buttons'
            )
        )
    )
);
