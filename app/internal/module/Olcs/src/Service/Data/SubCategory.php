<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataTrait;
use Dvsa\Olcs\Transfer\Query\SubCategory\GetList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Class SubCategory
 * @package Olcs\Service\Data
 */
class SubCategory extends AbstractDataService implements ListDataInterface
{
    use ListDataTrait;

    /**
     * @var string
     */
    protected $category;

    /*
     * @var string
     */
    protected $isScanCategory = null;

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
        $params['sort'] = 'subCategoryName';
        $params['order'] = 'ASC';

        $isScanCategory = $this->getIsScanCategory();
        if ($isScanCategory) {
            $params['isScanCategory'] = $isScanCategory;
        }

        $category = $this->getCategory();
        $key = 'all';
        if (!empty($category)) {
            $params['category'] = $category;
            $key = $category;
        }

        if (is_null($this->getData($key))) {

            $dtoData = GetList::create($params);
            $response = $this->handleQuery($dtoData);
            if (!$response->isOk()) {
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
     * @param array $data
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

    /**
     * Look up an item's description by its ID
     *
     * @param int $id
     *
     * @return string
     */
    public function getDescriptionFromId($id)
    {
        return $this->getPropertyFromKey('id', 'subCategoryName', $id);
    }

    /**
     * Get isScanCategory
     *
     * @return string
     */
    public function getIsScanCategory()
    {
        return $this->isScanCategory;
    }

    /**
     * Set isScanCategory
     *
     * @param string
     * @return \Olcs\Service\Data\SubCategory
     */
    public function setIsScanCategory($isScanCategory)
    {
        $this->isScanCategory = $isScanCategory;
        return $this;
    }
}
