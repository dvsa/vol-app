<?php

/**
 * Error Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Zend\Http\Response;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * Error Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ErrorController extends AbstractActionController
{
    public function notFoundAction()
    {
        $view = new ViewModel(['stopRedirect' => true]);
        $view->setTemplate('error/404');

        $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);

        return $view;
    }

    public function serverErrorAction()
    {
        $view = new ViewModel(['stopRedirect' => true]);
        $view->setTemplate('error/index');

        $this->getResponse()->setStatusCode(Response::STATUS_CODE_500);

        return $view;
    }
}
