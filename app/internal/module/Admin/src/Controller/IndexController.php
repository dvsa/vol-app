<?php
/**
 * IndexController
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/home.phtml');
        return $view;
    }
}