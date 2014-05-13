<?php

/**
 * Application journey config
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
return array(
    'Application' => array(
        'identifier' => 'applicationId',
        'completionService' => 'ApplicationCompletion',
        'completionStatusMap' => array(
            0 => '',
            1 => 'incomplete',
            2 => 'complete'
        ),
        'sections' => array(
            'TypeOfLicence' => array(
                'restriction' => array(
                    null,
                    'goods',
                    'psv',
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(
                    'OperatorLocation' => array(
                    ),
                    'OperatorType' => array(
                        'restriction' => array(
                            'gb'
                        ),
                        'required' => array(
                            'TypeOfLicence/OperatorLocation'
                        )
                    ),
                    'LicenceType' => array(
                        'required' => array(
                            'TypeOfLicence/OperatorType'
                        )
                    )
                )
            ),
            'YourBusiness' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(

                )
            ),
            'TaxiPhv' => array(
                'restriction' => array(
                    'psv-special-restricted'
                ),
                'subSections' => array(

                )
            ),
            'OperatingCentres' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted'
                ),
                'subSections' => array(

                )
            ),
            'TransportManagers' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard'
                ),
                'subSections' => array(

                )
            ),
            'VehicleSafety' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted'
                ),
                'subSections' => array(

                )
            ),
            'PreviousHistory' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted'
                ),
                'subSections' => array(

                )
            ),
            'ReviewDeclarations' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(

                )
            ),
            'PaymentSubmission' => array(
                'restriction' => array(
                    'goods-standard',
                    'psv-standard',
                    'goods-restricted',
                    'psv-restricted',
                    'psv-special-restricted'
                ),
                'subSections' => array(

                )
            )
        )
    )
);
