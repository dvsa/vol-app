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
    protected $lva = 'licence';
    protected string $location = 'external';

    /**
     * Index action
     *
     * @return \Laminas\Http\Response
     */
    #[\Override]
    public function indexAction()
    {
        // redirect to the login
        $identity = $this->currentUser()->getIdentity();
        if ($identity === null || $identity->isAnonymous()) {
            return $this->redirect()->toRoute('auth/login/GET');
        }
        if ($identity->isNotIdentified()) {
            throw new \Exception('Unable to retrieve identity');
        }

        // redir to the dashboard
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_DASHBOARD)) {
            if ($identity->getUserData()['eligibleForPrompt']) {
                return $this->redirect()->toRoute('prompt', [], ['code' => 303]);
            }

            return $this->redirect()->toRoute('dashboard', [], ['code' => 303]);
        }

        // redir to the bus reg page
        if ($identity->getUserType() === User::USER_TYPE_LOCAL_AUTHORITY) {
            return $this->redirect()->toRoute('busreg-registrations', [], ['code' => 303]);
        }

        return $this->redirect()->toRoute('search');
    }
}
