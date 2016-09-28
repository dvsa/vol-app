<?php

namespace Olcs\Service\Data;

/**
 * Class DocumentSubCategory
 *
 * @package Olcs\Service\Data
 */
class DocumentSubCategory extends SubCategory
{
    /**
     * Fetch list data
     *
     * @param array $params Params
     *
     * @return array
     */
    public function fetchListData($params)
    {
        $params['isDocCategory'] = 'Y';
        return parent::fetchListData($params);
    }
}
