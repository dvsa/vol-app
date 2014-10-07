<?php

/**
 * AbstractExternalController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractActionController;

/**
 * AbstractExternalController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractExternalController extends AbstractActionController
{
    /**
     * Get current user
     *
     * @return array
     */
    protected function getCurrentUser()
    {
        return $this->getEntityService('User')->getCurrentUser();
    }

    /**
     * Get current organisation
     *
     * @NOTE at the moment this, will just return the users first organisation, eventually the user will be able to
     *  select which organisation they are managing
     *
     * @return array
     */
    protected function getCurrentOrganisation()
    {
        $user = $this->getCurrentUser();
        return $this->getEntityService('Organisation')->getForUser($user['id']);
    }
}
