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
    protected $item;

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        //check whether we have a bus reg id or whether we're showing the list
        return $this->redirectToRoute('licence/bus-details', [], [], true);
    }

    /**
     * Renders the view
     *
     * @param string|\Zend\View\Model\ViewModel $view
     * @param string $pageTitle
     * @param string $pageSubTitle
     * @return \Zend\View\Model\ViewModel
     */
    public function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $this->pageLayout = 'bus';

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->getSection(),
            'item' => $this->getItem()
        );

        $layout = $this->getViewWithLicence(array_merge($variables, (array)$view->getVariables()));
        $layout->setTemplate($this->getLayoutFile());

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }
}
