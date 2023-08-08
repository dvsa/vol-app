<?php

namespace Olcs\Controller;

use Olcs\View\Model\ViewModel;

class WebdavController extends AbstractController
{
    public function authenticationSuccessfulAction()
    {
        $view = new ViewModel();
        $this->renderView($view, "Authentication Successful");
    }
}
