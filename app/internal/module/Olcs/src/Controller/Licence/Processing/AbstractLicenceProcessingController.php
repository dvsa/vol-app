<?php

/**
 * Abstract Licence Processing Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Olcs\Controller\Licence\LicenceController;
use Olcs\Helper\LicenceProcessingHelper;
use Zend\Navigation\Navigation;

/**
 * Abstract Licence Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractLicenceProcessingController extends LicenceController
{

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
     * Anything using renderView in this section will
     * inherit this layout
     *
     * @var string
     */
    protected $pageLayout = 'licence';

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

        $layout = $this->getViewWithLicence(
            array_merge($variables, (array)$view->getVariables())
        );
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
