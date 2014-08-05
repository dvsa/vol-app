<?php
/**
 * Printing Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * Printing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class PrintingController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('admin/page/printing');
        return $view;
    }
}
