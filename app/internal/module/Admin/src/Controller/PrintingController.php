<?php
/**
 * Printing Controller
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * Printing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PrintingController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('printing/index');
        return $view;
    }
}
