<?php

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Common\Controller\Lva\Traits\CommonApplicationControllerTrait;

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ApplicationControllerTrait
{
    use ExternalControllerTrait,
        CommonApplicationControllerTrait;

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $applicationId = $this->getApplicationId();

        if (!$this->isApplicationNew($applicationId)) {
            return $this->notFoundAction();
        }

        return $this->checkForRedirect($applicationId);
    }

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
        $organisationId = $this->getCurrentOrganisationId();

        $doesBelong = $this->getServiceLocator()->get('Entity\Application')
            ->doesBelongToOrganisation($applicationId, $organisationId);

        if (!$doesBelong) {
            $this->addErrorMessage('application-no-access');
        }

        return $doesBelong;
    }
}
