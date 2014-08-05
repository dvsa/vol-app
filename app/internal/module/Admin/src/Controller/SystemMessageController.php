<?php
/**
 * System Message Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * System Message Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class SystemMessageController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/system-message.phtml');
        return $view;
    }
}
