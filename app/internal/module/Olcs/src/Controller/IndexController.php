<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
class IndexController extends AbstractActionController
{

    /**
     * @codeCoverageIgnore
     */
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('index/home.phtml');
        return $view;
    }
}
