<?php

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\VehicleSafety;

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends VehicleSafetyController
{
    /**
     * Redirect to the first section
     *
     * @return Response
     */
    public function indexAction()
    {
        return $this->renderSection();
    }
}
