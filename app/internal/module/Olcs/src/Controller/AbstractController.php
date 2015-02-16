<?php

/**
 * Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\Traits as CommonTraits;
use Olcs\Controller\Traits as OlcsTraits;
use Common\Controller\AbstractActionController;
use Zend\Session\Container;

/**
 * Abstract Controller
 */
class AbstractController extends AbstractActionController
{
    use CommonTraits\ViewHelperManagerAware;
    use OlcsTraits\ListDataTrait;

    /**
     * Gets a variable from the route
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromRoute($param, $default = null)
    {
        return $this->params()->fromRoute($param, $default);
    }

    /**
     * Gets a variable from postdata
     *
     * @param string $param
     * @param mixed $default
     * @return type
     */
    public function fromPost($param, $default = null)
    {
        return $this->params()->fromPost($param, $default);
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

    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $view = parent::renderView($view, $pageTitle, $pageSubTitle);

        return $view;
    }
}
