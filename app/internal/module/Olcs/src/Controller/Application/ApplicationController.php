<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Traits;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractController
{
    use Traits\LicenceControllerTrait;

    public function indexAction()
    {
        $this->pageLayout = 'application';

        $view = $this->getViewWithLicence();
        $view->setTemplate('application/index');

        return $this->renderView($view);
    }
}
