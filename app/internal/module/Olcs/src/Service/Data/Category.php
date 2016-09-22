<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Data\ListDataTrait;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\Category\GetList;

/**
 * Class Category
 *
 * @package Olcs\Service\Data
 */
class Category extends AbstractDataService implements ListDataInterface
{
    use ListDataTrait;

    /*
     * @var string
     */
    protected $isScanCategory = null;

    /**
     * Fetch list data
     *
     * @param array $params Params
     *
     * @return array
     * @throw UnexpectedResponseException
     */
    public function fetchListData($params)
    {
        if (is_null($this->getData('categories'))) {
            $params['sort'] = 'description';
            $params['order'] = 'ASC';

            $isScanCategory = $this->getIsScanCategory();

            if ($isScanCategory) {
                $params['isScanCategory'] = $isScanCategory;
            }

            $dtoData = GetList::create($params);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('categories', false);
            $result = $response->getResult();

            if (isset($result['results'])) {
                $this->setData('categories', $result['results']);
            }
        }

        return $this->getData('categories');
    }

    /**
     * Get id from handle
     *
     * @param string $handle Handle
     *
     * @return string|null
     */
    public function getIdFromHandle($handle)
    {
        return $this->getPropertyFromKey('handle', 'id', $handle);
    }

    /**
     * Look up an item's description by its ID
     *
     * @param int $id Id
     *
     * @return string|null
     */
    public function getDescriptionFromId($id)
    {
        return $this->getPropertyFromKey('id', 'description', $id);
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
     * @param string $isScanCategory Is scan category
     *
     * @return \Olcs\Service\Data\Category
     */
    public function setIsScanCategory($isScanCategory)
    {
        $this->isScanCategory = $isScanCategory;
        return $this;
    }
}
