<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Class Category
 *
 * @package Olcs\Service\Data
 */
class Category extends AbstractListDataService
{
    const TYPE_IS_SCAN = 1;
    const TYPE_IS_DOC = 2;
    const TYPE_IS_TASK = 3;

    protected static $sort = 'description';
    protected static $order = 'ASC';

    /** @var  int */
    protected $catType;
    /** @var  bool */
    protected $isOnlyWithItems = false;

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
        $data = (array)$this->getData('categories');

        if (0 !== count($data)) {
            return $data;
        }

        $params = array_filter(
            [
                'sort' => self::$sort,
                'order' => self::$order,
                'isScanCategory' => (self::TYPE_IS_SCAN === $this->catType ? 'Y' : null),
                'isDocCategory' => (self::TYPE_IS_DOC === $this->catType ? 'Y' : null),
                'isTaskCategory' => (self::TYPE_IS_TASK === $this->catType ? 'Y' : null),
                'isOnlyWithItems' => ($this->isOnlyWithItems ? 'Y' : null),
            ]
        );

        $response = $this->handleQuery(
            TransferQry\Category\GetList::create($params)
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData('categories', (isset($result['results']) ? $result['results'] : null));

        return $this->getData('categories');
    }

    /**
     * Set category type
     *
     * @param string $catType Type of Category
     *
     * @return $this
     */
    public function setCategoryType($catType)
    {
        $this->catType = $catType;

        return $this;
    }

    /**
     * Get category type
     *
     * @return int
     */
    public function getCategoryType()
    {
        return $this->catType;
    }

    /**
     * Set is show caterories without items
     *
     * @param bool $isWithItems If true, then show options only with items
     *
     * @return $this
     */
    public function setIsOnlyWithItems($isWithItems)
    {
        $this->isOnlyWithItems = $isWithItems;

        return $this;
    }

    /**
     * Get is show caterories without items
     *
     * @return bool
     */
    public function getIsOnlyWithItems()
    {
        return $this->isOnlyWithItems;
    }
}
