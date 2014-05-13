<?php

/**
 * OperatingCentres Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\OperatingCentres;

use SelfServe\Controller\Application\ApplicationController;

/**
 * OperatingCentres Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentresController extends ApplicationController
{
    /**
     * Set the service for the "Free" save behaviour
     *
     * @var string
     */
    protected $service = '';

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
