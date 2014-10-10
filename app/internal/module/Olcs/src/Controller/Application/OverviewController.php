<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Zend\View\Model\ViewModel;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractApplicationController
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        $content = new ViewModel();
        $content->setTemplate('application/overview');

        return $this->render($content);
    }
}
