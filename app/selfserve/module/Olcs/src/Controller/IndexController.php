<?php

namespace Olcs\Controller;

use Common\Controller\Lva\AbstractController;
use Common\Rbac\User;
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
        // redirect to the login
        $identity = $this->currentUser()->getIdentity();
        if ($identity === null || $identity->isAnonymous()) {
            return $this->redirect()->toRoute('auth/login');
        }

        // redir to the dashboard
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_DASHBOARD)) {
            return $this->redirect()->toRoute('dashboard', [], ['code' => 303]);
        }

        // redir to the bus reg page
        if ($identity->getUserType() === User::USER_TYPE_LOCAL_AUTHORITY) {
            return $this->redirect()->toRoute('bus-registration', [], ['code' => 303]);
        }

        return $this->redirect()->toRoute('search');
    }
}
