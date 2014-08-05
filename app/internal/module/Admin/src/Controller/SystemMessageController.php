<?php
/**
 * System Message Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * System Message Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class SystemMessageController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('admin/page/system-message');
        return $view;
    }
}
