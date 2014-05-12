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
        'sections' => array(
            'TypeOfLicence' => array(
                'subSections' => array(
                    'OperatorLocation' => array(

                    ),
                    'OperatorType' => array(

                    ),
                    'LicenceType' => array(

                    )
                )
            )
        )
    )
);
