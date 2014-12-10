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

    private $searchForm;

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

    public function setSearchForm($form)
    {
        $this->searchForm = $form;
        return $this;
    }

    /**
     * Gets the search form for the header, it is cached on the object so that the search query is maintained
     */
    public function getSearchForm()
    {
        if ($this->searchForm === null) {
            $this->searchForm = $this->getFormClass('HeaderSearch');
            if ($this->searchForm->has('csrf')) {
                $this->searchForm->remove('csrf');
            }

            $container = new Container('search');
            $this->searchForm->bind($container);
        }

        return $this->searchForm;
    }

    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $view = parent::renderView($view, $pageTitle, $pageSubTitle);

        $view->setVariable('searchForm', $this->getSearchForm());

        return $view;
    }
}
