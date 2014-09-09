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
    use Traits\BusControllerTrait;

    protected $layoutFile = 'licence/bus/layout';
    protected $subNavRoute;
    protected $section;

    public function indexAction()
    {
        //check whether we have a bus reg id or whether we're showing the list
        return $this->redirectToRoute('licence/bus-details', [], [], true);
    }

    public function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $this->pageLayout = 'bus';

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->getSection()
        );

        $layout = $this->getViewWithLicence(array_merge($variables, (array)$view->getVariables()));
        $layout->setTemplate($this->getLayoutFile());

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }
}
