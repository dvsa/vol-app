<?php

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractLvaController;

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractExternalController extends AbstractLvaController
{
    /**
     * Set the location
     *
     * @var string
     */
    protected $location = 'external';

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
     * @NOTE at the moment this will just return the users first organisation,
     * eventually the user will be able to select which organisation they are managing
     *
     * @return array
     */
    protected function getCurrentOrganisation()
    {
        $user = $this->getCurrentUser();
        return $this->getEntityService('Organisation')->getForUser($user['id']);
    }

    /**
     * Get current organisation ID only
     *
     * @return int|null
     */
    protected function getCurrentOrganisationId()
    {
        $organisation = $this->getCurrentOrganisation();
        return (isset($organisation['id'])) ? $organisation['id'] : null;
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if (!$this->checkAccess($lvaId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->goToOverview($lvaId);
        }
    }
}
