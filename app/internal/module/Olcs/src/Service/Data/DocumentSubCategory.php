<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataTrait;

/**
 * Class DocumentSubCategory
 * @package Olcs\Service\Data
 */
class DocumentSubCategory extends AbstractData implements ListDataInterface
{
    use ListDataTrait;

    /**
     * @var string
     */
    protected $serviceName = 'DocumentSubCategory';

    /**
     * @var string
     */
    protected $category;

    /**
     * @param string $category
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
     */
    public function fetchListData($params)
    {
        $category = $this->getCategory();
        $key = 'all';
        if (!empty($category)) {
            $params['category'] = $category;
            $key = $category;
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
