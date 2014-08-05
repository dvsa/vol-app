<?php
/**
 * User Management Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * User Management Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class UserManagementController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('admin/page/user-management');
        return $view;
    }
}
