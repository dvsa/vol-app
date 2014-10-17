<?php

/**
 * Abstract Licence Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob.caiger@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Zend\Form\Form;
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

        $doesBelong = $this->getServiceLocator()->get('Entity\Licence')
            ->doesBelongToOrganisation($licenceId, $organisation['id']);

        if ($doesBelong) {
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
    protected function getLicenceId($lva = null)
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
        return $this->getServiceLocator()->get('Entity\Licence')->getTypeOfLicenceData($this->getLicenceId());
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

    /**
     * Render the section
     *
     * @param string $titleSuffix
     * @param \Zend\Form\Form $form
     * @return \Common\View\Model\Section
     */
    protected function render($titleSuffix, Form $form = null)
    {
        $form->get('form-actions')->remove('saveAndContinue');

        return parent::render($titleSuffix, $form);
    }
}
