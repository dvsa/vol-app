<?php
/**
 * IndexController
 */

namespace Admin\Controller;

use Common\Controller\FormActionController;

/**
 * IndexController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class IndexController extends FormActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('home');
        return $view;
    }
}
