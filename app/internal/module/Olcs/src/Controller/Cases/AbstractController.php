<?php

/**
 * Abstract Case Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */

namespace Olcs\Controller\Cases;

use Common\Controller\AbstractSectionController as CommonAbstractSectionController;
use Olcs\Controller\Traits\CaseControllerTrait as CaseControllerTrait;
use Olcs\Controller as OlcsController;

/**
 * Abstract Case Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class AbstractController extends OlcsController\CrudAbstract
{
    use CaseControllerTrait;

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
}