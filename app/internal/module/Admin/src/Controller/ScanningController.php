<?php
/**
 * Scanning Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Scanning Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ScanningController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/scanning.phtml');
        return $view;
    }
}
