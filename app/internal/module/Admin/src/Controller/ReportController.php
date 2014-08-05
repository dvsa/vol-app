<?php
/**
 * Report Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Report Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ReportController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/report.phtml');
        return $view;
    }
}
