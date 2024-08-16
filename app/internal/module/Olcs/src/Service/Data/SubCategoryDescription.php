<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Class SubCategoryDescription
 *
 * @package Olcs\Service\Data
 */
class SubCategoryDescription extends AbstractListDataService
{
    protected static $sort = 'description';
    protected static $order = 'ASC';

    /**
     * @var string
     */
    protected $subCategory;

    /**
     * Set sub category
     *
     * @param string $subCategory Sub category
     *
     * @return $this
     */
    public function setSubCategory($subCategory)
    {
        $this->subCategory = $subCategory;
        return $this;
    }

    /**
     * Get sub category
     *
     * @return string
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }

    /**
     * Fetch list data
     *
     * @param array $context Parameters
     *
     * @return array
     */
    public function fetchListData($context = null)
    {
        $subCatId = (int)$this->getSubCategory();

        $key = (0 !== $subCatId ? $subCatId : 'all');

        //  check data in cache
        $data = (array)$this->getData($key);
        if (0 !== count($data)) {
            return $data;
        }

        $params = array_filter(
            [
                'sort' => self::$sort,
                'order' => self::$order,
                'subCategory' => $subCatId,
            ]
        );

        $response = $this->handleQuery(
            TransferQry\SubCategoryDescription\GetList::create($params)
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        //  store to cache
        $result = $response->getResult();

        $this->setData($key, ($result['results'] ?? null));

        return $this->getData($key);
    }
}
