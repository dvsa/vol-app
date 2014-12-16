<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataTrait;

/**
 * Class SubCategoryDescription
 * @package Olcs\Service\Data
 */
class SubCategoryDescription extends AbstractData implements ListDataInterface
{
    use ListDataTrait;

    /**
     * @var string
     */
    protected $serviceName = 'SubCategoryDescription';

    /**
     * @var string
     */
    protected $subCategory;

    /**
     * @param string $subCategory
     * @return $this
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
     */
    public function fetchListData($params)
    {
        $subCategory = $this->getSubCategory();

        $key = 'all';
        if (!empty($subCategory)) {
            $params['subCategory'] = $subCategory;
            $key = $subCategory;
        }

        if (is_null($this->getData($key))) {
            $data = $this->getRestClient()->get('', $params);
            $this->setData($key, false);
            if (isset($data['Results'])) {
                $this->setData($key, $data['Results']);
            }
        }

        return $this->getData($key);
    }
}
