<?php

/**
 * YourBusiness Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\Application\YourBusiness;

use SelfServe\Controller\Application\ApplicationController;

/**
 * YourBusiness Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class YourBusinessController extends ApplicationController
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
