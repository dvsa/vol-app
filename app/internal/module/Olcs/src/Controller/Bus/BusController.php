<?php

/**
 * Bus Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Common\Controller\Traits;

/**
 * Bus Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusController extends OlcsController\CrudAbstract
{
    use ControllerTraits\BusControllerTrait;
    use Traits\ViewHelperManagerAware;

    /* bus controller properties */
    protected $layoutFile = 'layout/bus-registration-subsection';
    protected $subNavRoute;
    protected $section;
    protected $item;

    /* properties required by CrudAbstract */

    /**
     * Identifier name from route
     *
     * @var string
     */
    protected $identifierName = 'busRegId';

    /**
     * Holds the form name
     *
     * @var string
     */
    protected $formName = 'none';

    /**
     * The current page's extra layout, over and above the
     * standard base template, a sibling of the base though.
     *
     * @var string
     */
    protected $pageLayout = 'bus-registrations-section';

    /**
     * Holds the service name
     *
     * @var string
     */
    protected $service = 'BusReg';

    /**
     * Holds an array of variables for the
     * default index list page.
     */
    protected $listVars = [
        'licence',
        'busRegId'
    ];

    /**
     * Holds the Data Bundle
     *
     * @var array
     */
    protected $dataBundle = array(
        '',
    );

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
        $this->pageLayout = 'bus-registrations-section';

        $variables = array(
            'navigation' => $this->getSubNavigation(),
            'section' => $this->getSection(),
            'item' => $this->getItem()
        );

        $layout = $this->getViewWithBusReg(array_merge($variables, (array)$view->getVariables()));
        $layout->setTemplate($this->getLayoutFile());

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }

    /**
     * Sets the table filters.
     *
     * @param mixed $filters
     */
    public function setTableFilters($filters)
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')->set($filters);
    }
}
