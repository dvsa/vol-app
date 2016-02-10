<?php

namespace Olcs\Service\Data;

/**
 * Class DocumentCategory
 * @package Olcs\Service\Data
 */
class DocumentCategory extends Category
{
    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
     */
    public function fetchListData($params)
    {
        $params['isDocCategory'] = 'Y';
        return parent::fetchListData($params);
    }
}
