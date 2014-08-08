<?php
/**
 * Financial Standing Controller
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * Financial Standing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class FinancialStandingController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('financial-standing/index');
        return $view;
    }
}
