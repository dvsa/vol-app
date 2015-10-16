<?php

/**
 * Cookies Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Cookies Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CookiesController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('pages/placeholder');

        return $view;
    }
}
