<?php

/**
 * Continuation Controller
 */
namespace Admin\Controller;

use Common\Controller\AbstractActionController;

/**
 * Continuation Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

class ContinuationController extends AbstractActionController
{
    public function indexAction()
    {
        $view = $this->getView();
        $view->setTemplate('continuation/index');
        return $view;
    }
}
