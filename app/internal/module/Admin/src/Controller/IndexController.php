<?php

/**
 * IndexController
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');

        $this->setNavigationId('admin-dashboard');

        return $this->renderView($view, 'Admin');
    }
}
