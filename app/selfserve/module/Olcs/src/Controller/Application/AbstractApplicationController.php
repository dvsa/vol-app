<?php

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractExternalController;

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends AbstractExternalController
{
    /**
     * Check if the user has access to the application
     *
     * @NOTE We might want to consider caching this information within the session, to save making this request on each
     *  section
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function checkAccess($applicationId)
    {
        $organisation = $this->getCurrentOrganisation();

        if ($this->getEntityService('Application')->doesBelongToOrganisation($applicationId, $organisation['id'])) {
            return true;
        }

        $this->addErrorMessage('application-no-access');
        return false;
    }

    /**
     * Get licence id
     *
     * @param int $applicationId
     * @return int
     */
    protected function getLicenceId($applicationId)
    {
        return $this->getEntityService('Application')->getLicenceIdForApplication($applicationId);
    }
}
