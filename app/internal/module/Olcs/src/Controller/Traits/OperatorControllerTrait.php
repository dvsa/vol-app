<?php

/**
 * Operator Controller Trait
 */
namespace Olcs\Controller\Traits;

/**
 * Operator Controller Trait
 */
trait OperatorControllerTrait
{
    /**
     * Get view with Operator
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithOrganisation($variables = [])
    {
        $organisationId = $this->params()->fromRoute('organisation');

        if ($organisationId) {
            $org = $this->getBusinessDetailsData($organisationId);
            $this->pageTitle = isset($org['name']) ? $org['name'] : '';
            $variables['disable'] = false;
        } else {
            $org = null;
            $variables['disable'] = true;
            $variables['hideQuickActions'] = true;
        }
        $variables['organisation'] = $org;
        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }

    /**
     * @todo this needs migrating, should've been part of OLCS-9692?
     */
    protected function getBusinessDetailsData($organisationId)
    {
        return $this->getServiceLocator()->get('Entity\Organisation')->getBusinessDetailsData($organisationId);
    }

    /**
     * Gets the main navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    public function getNavigation()
    {
        return $this->getServiceLocator()->get('Navigation');
    }

    /**
     * Gets the sub navigation
     *
     * @return \Zend\Navigation\Page\Mvc
     */
    public function getSubNavigation()
    {
        return $this->getNavigation()->findOneBy('id', $this->getSubNavRoute());
    }

    /**
     * Returns the sub nav route
     *
     * @return string
     */
    public function getSubNavRoute()
    {
        return $this->subNavRoute;
    }
}
