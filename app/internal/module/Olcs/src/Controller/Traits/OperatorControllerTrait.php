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
     * Renders the view with layout
     *
     * @param string|\Zend\View\Model\ViewModel $view
     * @param string $pageTitle
     * @param string $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    public function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        if (!empty($this->getLayoutFile())) {
            $variables = array(
                'navigation' => $this->getSubNavigation(),
            );

            $layout = $this->getViewWithOrganisation(array_merge($variables, (array)$view->getVariables()));
            $layout->setTemplate($this->getLayoutFile());

            $layout->addChild($view, 'content');

            return parent::renderView($layout, $pageTitle, $pageSubTitle);
        }

        return parent::renderView($view, $pageTitle, $pageSubTitle);
    }

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

    /**
     * Returns the layout file
     *
     * @return string
     */
    public function getLayoutFile()
    {
        return $this->layoutFile;
    }
}
