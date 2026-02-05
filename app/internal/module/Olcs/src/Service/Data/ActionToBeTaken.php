<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\RefData;

/**
 * Action To Be Taken data service
 *
 * @package Olcs\Service\Data
 */
class ActionToBeTaken extends RefData
{
    /**
     * Fetch list data
     *
     * @param array $category Category
     *
     * @return array
     * @throws DataServiceException
     */
    #[\Override]
    public function fetchListData($category = null)
    {
        return parent::fetchListData('ptr_action_to_be_taken');
    }
}
