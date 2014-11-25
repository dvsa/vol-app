<?php

/**
 * Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\Traits;
use Common\Controller\AbstractActionController;
use Zend\Session\Container;

/**
 * Abstract Controller
 */
class AbstractController extends AbstractActionController
{
    use Traits\ViewHelperManagerAware;

    const MAX_LIST_DATA_LIMIT = 100;

    private $searchForm;

    /**
     * Retrieve some data from the backend and convert it for use in
     * a select. Optionally provide some search data to filter the
     * returned data too.
     */
    protected function getListData($entity, $data = array(), $titleKey = 'name', $primaryKey = 'id', $showAll = 'All')
    {
        $data['limit'] = self::MAX_LIST_DATA_LIMIT;
        $data['sort'] = $titleKey;  // AC says always sort alphabetically
        $response = $this->makeRestCall($entity, 'GET', $data);

        if ($showAll !== false) {
            $final = array('' => $showAll);
        } else {
            $final = array();
        }

        if (isset($response['Results']) && is_array($response['Results'])) {
            foreach ($response['Results'] as $result) {
                $key = $result[$primaryKey];
                $value = $result[$titleKey];

                $final[$key] = $value;
            }
        }
        return $final;
    }

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
