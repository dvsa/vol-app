<?php
/**
 * Report Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * Report Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ReportController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('report/index');
        return $view;
    }
}
