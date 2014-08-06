<?php
/**
 * Report Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * Report Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ReportController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('report/index');
        return $view;
    }
}
