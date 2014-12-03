<?php

namespace Olcs\Controller\Traits;

use Zend\Navigation\Navigation;

/**
 * ProcessingControllerTrait
 *
 * @package Olcs\Controller
 * @author Dan Eggleston <dan@stolenegg.com>
 */
trait ProcessingControllerTrait
{

    /**
     * Holds the current section
     *
     * @var string
     */
    protected $section = '';

    /**
     * Holds the processing helper
     *
     * @var \Olcs\Helper\AbstractProcessingHelper
     */
    protected $processingHelper;

    /**
     * Holds the 'parent' entity name, e.g. licence/application/case
     * rather than note/task
     *
     * @var string
     */
    protected $entity;

    /**
     * @return string
     */
    public function getEntityName()
    {
        return $this->entity;
    }

    /**
     * Get the processing helper
     *
     * @return \Olcs\Helper\AbstractProcessingHelper
     */
    protected function getProcessingHelper()
    {
        if (empty($this->processingHelper)) {
            $this->processingHelper = new $this->helperClass;
        }

        return $this->processingHelper;
    }

    /**
     * Get sub navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    protected function getSubNavigation()
    {
        $navigation = new Navigation($this->getNavigationConfig());

        $router = $this->getServiceLocator()->get('router');

        foreach ($navigation->getPages() as $page) {
            $page->setRouter($router);
        }

        return $navigation;
    }

    /**
     * Extend the render view method
     *
     * @param \Zend\View\Model\ViewModel $view
     * @param string $pageTitle
     * @param string $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        // @NOTE it's not ideal repeating logic from the parent renderView
        // method in this one but it's the quickest way out of this method
        // plus, even though it's not particularly DRY, we know that there's
        // nothing else we can possibly do to a terminal view so our parent
        // method couldn't help us out anyway
        if ($view->terminate()) {
            return $view;
        }

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->section
        );

        $layout = $this->getProcessingLayout($view, $variables);

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }
}
