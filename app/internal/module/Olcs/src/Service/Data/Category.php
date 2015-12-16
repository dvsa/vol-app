<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataTrait;
use Dvsa\Olcs\Transfer\Query\Category\GetList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Class Category
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
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $params
     * @return array
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
            if ($response->isServerError() || $response->isClientError() || !$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }
            $this->setData('categories', false);
            if (isset($response->getResult()['results'])) {
                $this->setData('categories', $response->getResult()['results']);
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
     * @return \Olcs\Service\Data\Category
     */
    public function setIsScanCategory($isScanCategory)
    {
        $this->isScanCategory = $isScanCategory;
        return $this;
    }
}
