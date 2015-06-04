<?php
/**
 * Report Controller
 */

namespace Admin\Controller;

use Zend\View\Model\ViewModel;

/**
 * Report Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

class ReportController extends AbstractController
{
    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');

        $this->setNavigationId('admin-dashboard/admin-report');

        return $this->renderView($view, 'Admin');
    }
}
