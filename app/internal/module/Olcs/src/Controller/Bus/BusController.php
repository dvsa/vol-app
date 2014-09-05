<?php

/**
 * Bus Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Traits;

/**
 * Bus Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusController extends AbstractController
{
    use Traits\LicenceControllerTrait;

    public function indexAction()
    {
        //placeholder, forwards to service details page
        return $this->redirectToRoute('licence/bus/details', [], [], true);
    }

    protected function viewVars(
        $view,
        $subNavRoute,
        $layoutFile = 'licence/bus/layout',
        $pageTitle = null,
        $pageSubTitle = null
    ) {
        $this->pageLayout = 'bus';

        $variables = array(
            'navigation' => $this->getSubNavigation($subNavRoute),
            'section' => $this->getSection()
        );

        $layout = $this->getViewWithLicence(array_merge($variables, (array)$view->getVariables()));
        $layout->setTemplate($layoutFile);

        $layout->addChild($view, 'content');

        return $this->renderView($layout, $pageTitle, $pageSubTitle);
    }

    public function getNavigation()
    {
        return $this->getServiceLocator()->get('Navigation');
    }

    public function getSubNavigation($route)
    {
        return $this->getNavigation()->findOneBy('id', $route);
    }

    public function getSection()
    {
        return $this->section;
    }
}
