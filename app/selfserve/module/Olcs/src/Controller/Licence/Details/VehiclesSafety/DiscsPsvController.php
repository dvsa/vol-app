<?php

/**
 * Discs Psv Controller
 *
 * External - Licence - Discs PSV section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence\Details\VehiclesSafety;

use Common\Controller\Traits;
use Olcs\Controller\Licence\Details\AbstractLicenceDetailsController;

/**
 * Discs Psv Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DiscsPsvController extends AbstractLicenceDetailsController
{
    use Traits\GenericIndexAction,
        Traits\GenericAddAction;

    protected $inlineScripts = array('discs');

    /**
     * Define the section service to use
     *
     * @var string
     */
    protected $sectionServiceName = 'VehicleSafety\\InternalLicenceDiscsPsv';

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_vehicle-safety_discs-psv';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'discs_psv';

    /**
     * Bespoke sub actions
     */
    protected $bespokeSubActions = array(
        'replace',
        'void'
    );

    public function replaceAction()
    {
        return $this->renderSection();
    }

    public function voidAction()
    {
        return $this->renderSection();
    }
}
