<?php

/**
 * Abstract Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller;

use Common\Controller\AbstractController as CommonAbstractController;

/**
 * Abstract Controller
 */
class AbstractController extends CommonAbstractController
{
    const MAX_LIST_DATA_LIMIT = 100;

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
}
