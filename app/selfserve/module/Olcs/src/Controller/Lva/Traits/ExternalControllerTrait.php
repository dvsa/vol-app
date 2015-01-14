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
     * @param array $variables
     * @return \Common\View\Model\Section
     */
    protected function render($titleSuffix, Form $form = null, $variables = array())
    {
        $this->attachCurrentMessages();

        if ($titleSuffix instanceof ViewModel) {
            return $titleSuffix;
        }

        $sectionName = $titleSuffix;
        // overrides for any instance where the section name differs from the view template name
        $sectionOverrides = [
            'person' => 'people'
        ];
        if (array_key_exists($titleSuffix, $sectionOverrides)) {
            $sectionName = $sectionOverrides[$titleSuffix];
        }

        $progress = [];
        if (method_exists($this, 'getSectionStepProgress')) {
            // @todo make this a required method once implemented across all of LVA
            $progress = $this->getSectionStepProgress($sectionName);
        }

        $params = array_merge(
            array('title' => 'lva.section.title.' . $titleSuffix, 'form' => $form),
            $progress,
            $variables
        );

        $section = new Section($params);

        $template = $this->getRequest()->isXmlHttpRequest() ? 'ajax' : 'layout';

        $base = new ViewModel();
        $base->setTemplate('layout/' . $template)
            ->setTerminal(true)
            ->addChild($section, 'content');

        return $base;
    }
}
