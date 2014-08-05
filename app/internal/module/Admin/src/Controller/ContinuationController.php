<?php
/**
 * Continuation Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Continuation Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ContinuationController extends AbstractActionController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('admin/page/continuation.phtml');
        return $view;
    }
}
