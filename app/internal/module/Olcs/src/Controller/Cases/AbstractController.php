<?php

/**
 * Abstract Case Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech>
 */

namespace Olcs\Controller\Cases;

use Common\Controller\AbstractSectionController as CommonAbstractSectionController;
use Olcs\Controller\Traits\CaseControllerTrait as CaseControllerTrait;

/**
 * Abstract Case Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech>
 */
class AbstractController extends CommonAbstractSectionController
{
    use CaseControllerTrait;

    const MAX_LIST_DATA_LIMIT = 100;

    /**
     * Identifier name
     *
     * @var string
     */
    protected $identifierName = 'case';

    /**
     * The current page's extra layout, over and above the
     * standard base template
     *
     * @var string
     */
    protected $pageLayout = 'case';

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

        foreach ($response['Results'] as $result) {
            $key = $result[$primaryKey];
            $value = $result[$titleKey];

            $final[$key] = $value;
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
     * Extend the render view method
     *
     * @param type $view
     */
    protected function renderView($view, $pageTitle = null, $pageSubTitle = null)
    {
        $layout = $this->getView($view->getVariables());

        $layout->setTemplate('case/layout');

        $this->maybeAddScripts($layout);

        $layout->addChild($view, 'content');

        return parent::renderView($layout, $pageTitle, $pageSubTitle);
    }
}