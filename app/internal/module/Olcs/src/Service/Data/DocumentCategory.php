<?php

namespace Olcs\Service\Data;

/**
 * Class DocumentCategory
 *
 * @package Olcs\Service\Data
 */
class DocumentCategory extends Category
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
