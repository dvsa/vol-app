<?php

/**
 * Error Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Controller;

use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Model\ViewModel;

/**
 * Error Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ErrorController extends LaminasAbstractActionController
{
    #[\Override]
    public function notFoundAction()
    {
        $view = new ViewModel(['stopRedirect' => true]);
        $view->setTemplate('error/404');

        $this->getResponse()->setStatusCode(Response::STATUS_CODE_404);

        return $view;
    }

    public function serverErrorAction()
    {
        $view = new ViewModel(
            [
                'id' => $this->params()->fromQuery('id'),
                'stopRedirect' => true,
            ]
        );
        $view->setTemplate('error/index');

        $this->getResponse()->setStatusCode(Response::STATUS_CODE_500);

        return $view;
    }
}
