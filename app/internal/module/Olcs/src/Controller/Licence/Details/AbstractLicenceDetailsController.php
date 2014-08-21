<?php

/**
 * Abstract LicenceDetails Controller
 */
namespace Olcs\Controller\Licence\Details;

use Olcs\Controller\AbstractController;
use Olcs\Helper\LicenceDetailsHelper;
use Olcs\Controller\Traits\LicenceController;
use Zend\Navigation\Navigation;

/**
 * Abstract LicenceDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractLicenceDetailsController extends AbstractController
{
    use LicenceController;

    /**
     * Holds the current section
     *
     * @var string
     */
    protected $section = '';

    /**
     * Holds the licence details helper
     *
     * @var \Olcs\Helper\LicenceDetailsHelper
     */
    protected $licenceDetailsHelper;

    /**
     * Get the licence details helper
     *
     * @return \Olcs\Helper\LicenceDetailsHelper
     */
    protected function getLicenceDetailsHelper()
    {
        if (empty($this->licenceDetailsHelper)) {
            $this->licenceDetailsHelper = new LicenceDetailsHelper();
        }

        return $this->licenceDetailsHelper;
    }

    /**
     * Extend the render view method
     *
     * @param type $view
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $this->pageLayout = 'licence';

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->section
        );

        $layout = $this->getViewWithLicence($variables);
        $layout->setTemplate('licence/details/layout');

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }

    /**
     * Get sub navigation
     */
    protected function getSubNavigation()
    {
        $licence = $this->getLicence();

        $navigationConfig = $this->getLicenceDetailsHelper()->getNavigation(
            $licence['id'],
            $licence['goodsOrPsv']['id'],
            $licence['licenceType']['id'],
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
