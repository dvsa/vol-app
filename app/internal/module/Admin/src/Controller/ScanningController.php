<?php
/**
 * Scanning Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * Scanning Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ScanningController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('scanning/index');
        return $view;
    }
}
