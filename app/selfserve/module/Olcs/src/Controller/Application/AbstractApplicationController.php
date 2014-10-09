<?php

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractExternalController;
use Common\Controller\Traits\Lva\ApplicationControllerTrait;

/**
 * EXTERNAL Abstract Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractApplicationController extends AbstractExternalController
{
    use ApplicationControllerTrait;

    /**
     * Holds the lva type
     *
     * @var string
     */
    protected $lva = 'application';

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
                ->toRoute('lva-' . $this->lva . '/' . $sections[$index + 1], array('id' => $this->getApplicationId()));
        }
    }
}
