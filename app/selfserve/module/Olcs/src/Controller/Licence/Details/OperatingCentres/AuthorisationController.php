<?php

/**
 * Authorisation Controller
 *
 * External - Licence section
 */
namespace Olcs\Controller\Licence\Details\OperatingCentres;

use Common\Controller\Traits\OperatingCentre;

/**
 * Authorisation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AuthorisationController extends OperatingCentresController
{
    use OperatingCentre\GenericLicenceAuthorisationSection,
        OperatingCentre\ExternalLicenceAuthorisationSection;

    /**
     * Northern Ireland Traffic Area Code
     */
    const NORTHERN_IRELAND_TRAFFIC_AREA_CODE = 'N';

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_operating-centres_authorisation';
}
