<?php

namespace Olcs\Service\Data;

/**
 * Class DocumentSubCategory
 * @package Olcs\Service\Data
 */
class DocumentSubCategory extends SubCategory
{
    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
     */
    public function fetchListData($params)
    {
        $params['isDoc'] = true;
        return parent::fetchListData($params);
    }
}
