<?php
/**
 * IndexController
 */

namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * IndexController
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class IndexController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('home');
        return $view;
    }
}
