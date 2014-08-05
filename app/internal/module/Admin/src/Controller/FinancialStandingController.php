<?php
/**
 * Financial Standing Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Financial Standing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class FinancialStandingController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/financial-standing.phtml');
        return $view;
    }
}
