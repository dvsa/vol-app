<?php

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Traits;

use Zend\Form\Form;
use Zend\View\Model\ViewModel;
use Common\View\Model\Section;

/**
 * Abstract External Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait ExternalControllerTrait
{
    /**
     * Redirect back to overview
     */
    protected function handleCancelRedirect($lvaId)
    {
        return $this->goToOverview($lvaId);
    }

    /**
     * Get current user
     *
     * @return array
     */
    protected function getCurrentUser()
    {
        return $this->getServiceLocator()->get('Entity\User')->getCurrentUser();
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
        return $this->getServiceLocator()->get('Entity\Organisation')->getForUser($user['id']);
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
     * @todo now this is a trait, we can't call parent due to sonar
     *
     * @param int $lvaId
     * @return null|\Zend\Http\Response
     */
    protected function checkForRedirect($lvaId)
    {
        if (!$this->checkAccess($lvaId)) {
            return $this->redirect()->toRoute('dashboard');
        }

        return parent::checkForRedirect($lvaId);
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
        $this->attachCurrentMessages();

        if ($titleSuffix instanceof ViewModel) {
            return $titleSuffix;
        }

        return new Section(array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form));
    }
}
