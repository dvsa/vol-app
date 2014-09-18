<?php

/**
 * Operating Centre Controller
 *
 * Internal - Licence section
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits;

/**
 * Operating Centre Controller
 */
class OperatingCentreController extends AbstractLicenceDetailsController
{
    use Traits\OperatingCentre\GenericLicenceAuthorisationSection;

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

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'operating_centre';
}
