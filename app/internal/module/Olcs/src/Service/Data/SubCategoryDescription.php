<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataTrait;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\SubCategoryDescription\GetList;

/**
 * Class SubCategoryDescription
 * @package Olcs\Service\Data
 */
class SubCategoryDescription extends AbstractDataService implements ListDataInterface
{
    use ListDataTrait;

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
            $dtoData = GetList::create($params);
            $response = $this->handleQuery($dtoData);
            if ($response->isServerError() || $response->isClientError() || !$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData($key, false);
            if (isset($response->getResult()['results'])) {
                $this->setData($key, $response->getResult()['results']);
            }
        }

        return $this->getData($key);
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
