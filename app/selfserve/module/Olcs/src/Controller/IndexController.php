<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\RefData;

/**
 * Index Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class IndexController extends AbstractController
{
    public function indexAction()
    {
        if ($this->currentUser()->getIdentity() === null) {
            // redirect to the login
            return $this->redirect()->toRoute('auth/login');
        }

        if (! $this->isGranted(RefData::PERMISSION_SELFSERVE_DASHBOARD)) {
            // logout the user
            return $this->redirect()->toRoute('auth/logout');
        }

        // redir to the dashboard
        return $this->redirect()->toRoute('dashboard', [], ['code' => 303]);
    }
}
