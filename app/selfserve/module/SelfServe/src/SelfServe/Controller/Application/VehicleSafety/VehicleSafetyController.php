<?php

/**
 * VehicleSafety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\VehicleSafety;

use SelfServe\Controller\Application\ApplicationController;

/**
 * VehicleSafety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleSafetyController extends ApplicationController
{
    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->goToFirstSubSection();
    }
}
