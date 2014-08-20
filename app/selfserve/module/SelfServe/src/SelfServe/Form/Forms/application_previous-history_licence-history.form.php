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
                    'prevHasLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevHasLicence',
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
                    'prevHadLicence' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevHadLicence',
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
                    'prevBeenRefused' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevBeenRefused',
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
                    'prevBeenRevoked' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevBeenRevoked',
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
                    'prevBeenAtPi' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevBeenAtPi',
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
                    'prevBeenDisqualifiedTc' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevBeenDisqualifiedTc',
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
                    'prevPurchasedAssets' => array(
                        'type' => 'yesNoRadio',
                        'label' => $prefix . '_prevPurchasedAssets',
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
