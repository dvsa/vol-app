<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * User Management Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class UserManagementController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('user-management/index');
        return $view;
    }
}
