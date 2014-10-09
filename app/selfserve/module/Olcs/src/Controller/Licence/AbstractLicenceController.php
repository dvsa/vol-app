<?php

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Olcs\Controller\AbstractExternalController;

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
abstract class AbstractLicenceController extends AbstractExternalController
{
    /**
     * Lva
     *
     * @var string
     */
    protected $lva = 'licence';

    /**
     * Hook into the dispatch before the controller action is executed
     */
    protected function preDispatch()
    {
        $licenceId = $this->getLicenceId();

        return $this->checkForRedirect($licenceId);
    }

    /**
     * Check if the user has access to the licence
     *
     * @NOTE We might want to consider caching this information within the session, to save making this request on each
     *  section
     *
     * @param int $licenceId
     * @return boolean
     */
    protected function checkAccess($licenceId)
    {
        $organisation = $this->getCurrentOrganisation();

        if ($this->getEntityService('Licence')->doesBelongToOrganisation($licenceId, $organisation['id'])) {
            return true;
        }

        $this->addErrorMessage('licence-no-access');
        return false;
    }

    /**
     * Get licence id
     *
     * @return int
     */
    protected function getLicenceId()
    {
        return $this->params('id');
    }

    /**
     * Get type of licence data
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        return $this->getEntityService('Licence')->getTypeOfLicenceData($this->getLicenceId());
    }
}
