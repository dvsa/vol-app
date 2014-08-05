<?php
/**
 * Printing Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Printing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PrintingController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/printing.phtml');
        return $view;
    }
}
