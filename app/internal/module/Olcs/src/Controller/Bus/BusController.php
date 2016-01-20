<?php

/**
 * Bus Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus;

use Olcs\Controller as OlcsController;
use Olcs\Controller\Traits as ControllerTraits;
use Common\Controller\Traits as CommonTraits;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Bus Controller
 *
 * @NOTE Made this abstract as it is never used as a concrete
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class BusController extends OlcsController\CrudAbstract implements BusRegControllerInterface, LeftViewProvider
{
    use ControllerTraits\BusControllerTrait;
    use CommonTraits\ViewHelperManagerAware;

    use CommonTraits\GenericRenderView {
        CommonTraits\GenericRenderView::renderView as parentRenderView;
    }

    /* bus controller properties */
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

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
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
        $this->maybeAddScripts($view);

        return $this->parentRenderView($view, $pageTitle, $pageSubTitle);
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

    /**
     * Load an array of script files which will be rendered inline inside a view
     *
     * @param array $scripts
     * @return array
     */
    protected function loadScripts($scripts)
    {
        return $this->getServiceLocator()->get('Script')->loadFiles($scripts);
    }

    /**
     * Optionally add scripts to view, if there are any
     *
     * @param ViewModel $view
     */
    protected function maybeAddScripts($view)
    {
        $scripts = $this->getInlineScripts();

        if (empty($scripts)) {
            return;
        }

        // this process defers to a service which takes care of checking
        // whether the script(s) exist
        $this->loadScripts($scripts);
    }

    protected function normaliseFormName($name, $ucFirst = false)
    {
        $name = str_replace([' ', '_'], '-', $name);

        $name = $this->getServiceLocator()->get('Helper\String')->dashToCamel($name);

        if (!$ucFirst) {
            return lcfirst($name);
        }

        return $name;
    }
}
