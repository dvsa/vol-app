<?php

/**
 * Abstract Licence Processing Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\AbstractController;
use Olcs\Helper\LicenceProcessingHelper;
use Olcs\Controller\Traits\LicenceControllerTrait;
use Zend\Navigation\Navigation;

/**
 * Abstract Licence Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractLicenceProcessingController extends AbstractController
{
    use LicenceControllerTrait;

    /**
     * Holds the current section
     *
     * @var string
     */
    protected $section = '';

    /**
     * Holds the licence processing helper
     *
     * @var \Olcs\Helper\LicenceProcessingHelper
     */
    protected $licenceProcessingHelper;

    /**
     * Get the licence processing helper
     *
     * @return \Olcs\Helper\LicenceProcessingHelper
     */
    protected function getLicenceProcessingHelper()
    {
        if (empty($this->licenceProcessingHelper)) {
            $this->licenceProcessingHelper = new LicenceProcessingHelper();
        }

        return $this->licenceProcessingHelper;
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
        $this->pageLayout = 'licence';

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->section
        );

        $layout = $this->getViewWithLicence($variables);
        $layout->setTemplate('licence/processing/layout');

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
        $licence = $this->getLicence();

        $navigationConfig = $this->getLicenceProcessingHelper()->getNavigation(
            $licence['id'],
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