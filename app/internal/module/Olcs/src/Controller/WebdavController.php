<?php

namespace Olcs\Controller;

use Olcs\View\Model\ViewModel;

/**
 * Class WebdavController
 *
 * @package Olcs\Controller
 */
class WebdavController extends AbstractController
{
    public function authenticationSuccessfulAction()
    {
        $view = new ViewModel();
        $this->renderView($view, "Authentication Successful");
    }
}
