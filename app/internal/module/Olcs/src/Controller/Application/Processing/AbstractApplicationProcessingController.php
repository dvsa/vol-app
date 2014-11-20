<?php

/**
 * Abstract Application Processing Controller
 */
namespace Olcs\Controller\Application\Processing;

use Olcs\Controller\Application\ApplicationController;
use Olcs\Helper\ApplicationProcessingHelper;
use Zend\Navigation\Navigation;

/**
 * Abstract Application Processing Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractApplicationProcessingController extends ApplicationController
{

    /**
     * Holds the current section
     *
     * @var string
     */
    protected $section = '';

    /**
     * Holds the application processing helper
     *
     * @var \Olcs\Helper\ApplicationProcessingHelper
     */
    protected $applicationProcessingHelper;

    /**
     * Anything using renderView in this section will
     * inherit this layout
     *
     * @var string
     */
    protected $pageLayout = 'application';

    /**
     * Get the application processing helper
     *
     * @return \Olcs\Helper\ApplicationProcessingHelper
     */
    protected function getApplicationProcessingHelper()
    {
        if (empty($this->applicationProcessingHelper)) {
            $this->applicationProcessingHelper = new ApplicationProcessingHelper();
        }

        return $this->applicationProcessingHelper;
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

        $layout = $this->getViewWithApplication(
            array_merge($variables, (array)$view->getVariables())
        );
        $layout->setTemplate('application/processing/layout');

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }

    /**
     * Get sub navigation
     *
     * @return \Zend\Navigation\Navigation
     */
    protected function getSubNavigation()
    {
        $application = $this->getApplication();

        $navigationConfig = $this->getApplicationProcessingHelper()->getNavigation(
            $application['id'],
            $this->section
        );

        $navigation = new Navigation($navigationConfig);

        $router = $this->getServiceLocator()->get('router');

        foreach ($navigation->getPages() as $page) {
            $page->setRouter($router);
        }

        return $navigation;
    }
}
