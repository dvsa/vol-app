<?php

namespace Olcs\Service\Data;

/**
 * Class TaskSubCategory
 * @package Olcs\Service\Data
 */
class TaskSubCategory extends SubCategory
{
    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
     */
    public function fetchListData($params)
    {
        $params['isTask'] = true;
        return parent::fetchListData($params);
    }
}
