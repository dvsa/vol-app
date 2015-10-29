<?php

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Dvsa\Olcs\Transfer\Query\Licence\Licence as LicenceQry;
use Olcs\Logging\Log\Logger;
use Zend\Form\Form;

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
trait LicenceControllerTrait
{
    use ExternalControllerTrait;

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
        $dto = LicenceQry::create(['id' => $licenceId]);
        $response = $this->handleQuery($dto);
        $data = $response->getResult();

        $usersOrganisation = $this->getCurrentOrganisationId();

        $doesBelong = $data['organisation']['id'] == $usersOrganisation;

        if (!$doesBelong) {
            $this->addErrorMessage('licence-no-access');

            $logData = [
                'Users Organisation' => $usersOrganisation,
                'Licence Data' => $data,
                'Response' => $response
            ];

            Logger::debug('**** REDIRECT TO DASHBOARD ****', ['data' => $logData]);
        }

        return $doesBelong;
    }

    /**
     * Get licence id
     *
     * @return int
     * @inheritdoc
     */
    protected function getLicenceId($lva = null)
    {
        return $this->getIdentifier();
    }

    /**
     * Complete a section and potentially redirect to the next
     * one depending on the user's choice
     *
     * @return \Zend\Http\Response
     */
    protected function completeSection($section)
    {
        $this->addSectionUpdatedMessage($section);

        return $this->goToOverviewAfterSave($this->getLicenceId());
    }

    protected function alterFormForLva(Form $form)
    {
        return $this->getServiceLocator()->get('LicenceLvaAdapter')->alterForm($form);
    }
}
