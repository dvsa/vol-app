<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * User Management Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class UserManagementController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/user-management.phtml');
        return $view;
    }
}
