<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Class SubCategory
 *
 * @package Olcs\Service\Data
 */
class SubCategory extends Category
{
    protected static $sort = 'subCategoryName';

    /** @var string */
    private $category;

    /**
     * Set category
     *
     * @param string $category Category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;
        return $this;
    }

    /**
     * Get category
     *
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Fetch list data
     *
     * @param array $context Parameters
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData($context = null)
    {
        $catId = (int)$this->getCategory();

        $key = (0 !== $catId ? $catId : 'all');

        //  check data in cache
        $data = (array)$this->getData($key);
        if (0 !== count($data)) {
            return $data;
        }

        //  build query
        $params = array_filter(
            [
                'category' => $catId,
                'sort' => self::$sort,
                'order' => self::$order,
                'isScanCategory' => (self::TYPE_IS_SCAN === $this->catType ? 'Y' : null),
                'isDocCategory' => (self::TYPE_IS_DOC === $this->catType ? 'Y' : null),
                'isTaskCategory' => (self::TYPE_IS_TASK === $this->catType ? 'Y' : null),
                'isOnlyWithItems' => ($this->isOnlyWithItems ? 'Y' : null),
                'isMessagingCategory'  => (self::TYPE_IS_TASK === $this->catType ? 'Y' : null),
            ]
        );

        $response = $this->handleQuery(
            TransferQry\SubCategory\GetList::create($params)
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        //  store to cache
        $result = $response->getResult();

        $this->setData($key, (isset($result['results']) ? $result['results'] : null));

        return $this->getData($key);
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['subCategoryName'];
        }

        return $optionData;
    }
}
