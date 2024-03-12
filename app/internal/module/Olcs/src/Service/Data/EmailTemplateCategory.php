<?php

namespace Olcs\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Class Email Template Categories
 *
 * @package Olcs\Service\Data
 */
class EmailTemplateCategory extends AbstractListDataService
{
    protected static $sort = 'description';
    protected static $order = 'ASC';

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
                'order' => self::$order
            ]
        );

        $response = $this->handleQuery(
            TransferQry\Template\TemplateCategories::create($params)
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData('categories', ($result['results'] ?? null));
        $data = $this->getData('categories');
        $data[] = [
            'description' => 'Header/Footer',
            'id' => 0
        ];

        return $data;
    }
}
