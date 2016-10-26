<?php

namespace Olcs\Controller\Lva\Traits;

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
     *
     * @return null|\Zend\Http\Response
     */
    protected function preDispatch()
    {
        $licenceId = $this->getLicenceId();

        return $this->checkForRedirect($licenceId);
    }

    /**
     * Get licence id
     *
     * @param int $lva Lva
     *
     * @inheritdoc
     * @return string
     */
    protected function getLicenceId($lva = null)
    {
        return $this->getIdentifier();
    }

    /**
     * Complete a section and potentially redirect to the next
     * one depending on the user's choice
     *
     * @param string $section Section
     *
     * @return \Zend\Http\Response
     */
    protected function completeSection($section)
    {
        return $this->goToOverviewAfterSave($this->getLicenceId());
    }

    /**
     * Alter form
     *
     * @param Form  $form Form
     * @param array $data Api/Form Data
     *
     * @return mixed
     */
    protected function alterFormForLva(Form $form, $data = null)
    {
        return $this->getServiceLocator()->get('LicenceLvaAdapter')->alterForm($form);
    }
}
