<?php

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractExternalController;
use Common\Service\Entity\ApplicationService;

/**
 * Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends AbstractExternalController
{
    /**
     * Holds the lva type
     *
     * @var string
     */
    protected $lva = 'application';

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
     * Check for redirect
     *
     * @param int $applicationId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($applicationId)
    {
        if (!$this->checkAccess($applicationId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        if ($this->isButtonPressed('cancel')) {
            return $this->goToOverview($applicationId);
        }
    }

    /**
     * Update application status
     *
     * @params int $applicationId
     */
    protected function updateCompletionStatuses($applicationId)
    {
        $this->getEntityService('ApplicationCompletion')->updateCompletionStatuses($applicationId);
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
        $organisation = $this->getCurrentOrganisation();

        if ($this->getEntityService('Application')->doesBelongToOrganisation($applicationId, $organisation['id'])) {
            return true;
        }

        $this->addErrorMessage('application-no-access');
        return false;
    }

    /**
     * Check if the application is new
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function isApplicationNew($applicationId)
    {
        return $this->getApplicationType($applicationId) === ApplicationService::APPLICATION_TYPE_NEW;
    }

    /**
     * Check if the application is variation
     *
     * @param int $applicationId
     * @return boolean
     */
    protected function isApplicationVariation($applicationId)
    {
        return $this->getApplicationType($applicationId) === ApplicationService::APPLICATION_TYPE_VARIATION;
    }

    /**
     *
     * @param int $applicationId
     * @return int
     */
    protected function getApplicationType($applicationId)
    {
        return $this->getEntityService('Application')->getApplicationType($applicationId);
    }

    /**
     * Get application id
     *
     * @return int
     */
    protected function getApplicationId()
    {
        return $this->params('id');
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

    /**
     * Go to overview page
     *
     * @param int $applicationId
     * @return \Zend\Http\Response
     */
    protected function goToOverview($applicationId)
    {
        return $this->redirect()->toRoute($this->lva, array('id' => $applicationId));
    }

    /**
     * Redirect to the next section
     *
     * @param string $currentSection
     */
    protected function goToNextSection($currentSection)
    {
        $sections = $this->getAccessibleSections();

        $index = array_search($currentSection, $sections);

        // If there is no next section
        if (!isset($sections[$index + 1])) {
            return $this->goToOverview($this->getApplicationId());
        } else {
            return $this->redirect()
                ->toRoute($this->lva . '/' . $sections[$index + 1], array('id' => $this->getApplicationId()));
        }
    }

    /**
     * Get type of licence data
     *
     * @return array
     */
    protected function getTypeOfLicenceData()
    {
        $licenceId = $this->getLicenceId($this->getApplicationId());

        return $this->getEntityService('Licence')->getTypeOfLicenceData($licenceId);
    }
}
