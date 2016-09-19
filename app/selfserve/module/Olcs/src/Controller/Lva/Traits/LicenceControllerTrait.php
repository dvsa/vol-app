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
        return $this->goToOverviewAfterSave($this->getLicenceId());
    }

    protected function alterFormForLva(Form $form)
    {
        return $this->getServiceLocator()->get('LicenceLvaAdapter')->alterForm($form);
    }
}
