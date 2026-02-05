<?php

namespace Olcs\Service\Data\Letter;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Master Template data service
 *
 * @package Olcs\Service\Data\Letter
 */
class MasterTemplate extends AbstractListDataService
{
    protected static $sort = 'name';
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
        $data = (array)$this->getData('master-templates');

        if (0 !== count($data)) {
            return $data;
        }

        $params = [
            'sort' => self::$sort,
            'order' => self::$order,
            'page' => 1,
            'limit' => 100,
        ];

        $response = $this->handleQuery(
            TransferQry\Letter\MasterTemplate\GetList::create($params)
        );

        if (!$response->isOk()) {
            $body = $response->getBody();
            $errorMessage = 'Failed to fetch master templates: ' .
                ($body ?: 'HTTP ' . $response->getStatusCode());
            throw new DataServiceException($errorMessage);
        }

        $result = $response->getResult();

        $this->setData('master-templates', ($result['results'] ?? []));

        return $this->getData('master-templates');
    }

    /**
     * Format data
     *
     * @param array $data Data
     *
     * @return array
     */
    #[\Override]
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum['id']] = $datum['name'];
        }

        return $optionData;
    }
}
