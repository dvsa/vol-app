<?php
/**
 * Scanning Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * Scanning Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ScanningController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('admin/page/scanning');
        return $view;
    }
}
