<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataTrait;

/**
 * Class Category
 * @package Olcs\Service\Data
 */
class Category extends AbstractData implements ListDataInterface
{
    use ListDataTrait;

    /**
     * @var string
     */
    protected $serviceName = 'Category';

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
     */
    public function fetchListData($params)
    {
        $params['sort'] = 'description';

        if (is_null($this->getData('categories'))) {
            $data = $this->getRestClient()->get('', $params);
            $this->setData('categories', false);
            if (isset($data['Results'])) {
                $this->setData('categories', $data['Results']);
            }
        }

        return $this->getData('categories');
    }

    /**
     * @param $handle
     * @return null
     */
    public function getIdFromHandle($handle)
    {
        return $this->getPropertyFromKey('handle', 'id', $handle);
    }

    /**
     * Look up an item's description by its ID
     *
     * @param int $id
     *
     * @return string
     */
    public function getDescriptionFromId($id)
    {
        return $this->getPropertyFromKey('id', 'description', $id);
    }
}
