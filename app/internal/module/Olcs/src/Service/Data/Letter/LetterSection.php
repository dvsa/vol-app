<?php

namespace Olcs\Service\Data\Letter;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Letter Section data service
 *
 * @package Olcs\Service\Data\Letter
 */
class LetterSection extends AbstractListDataService
{
    protected static $sort = 'sectionKey';
    protected static $order = 'ASC';

    /**
     * Fetch list data
     *
     * @param array $context Parameters
     *
     * @return array
     * @throw DataServiceException
     */
    #[\Override]
    public function fetchListData($context = null)
    {
        $data = (array)$this->getData('letter-section');

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
            TransferQry\Letter\LetterSection\GetList::create($params)
        );

        if (!$response->isOk()) {
            $body = $response->getBody();
            $errorMessage = 'Failed to fetch letter sections: ' .
                ($body ? $body : 'HTTP ' . $response->getStatusCode());
            throw new DataServiceException($errorMessage);
        }

        $result = $response->getResult();

        $this->setData('letter-section', ($result['results'] ?? []));

        return $this->getData('letter-section');
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
            $name = $datum['currentVersion']['name']
                ?? $datum['name']
                ?? ('Section #' . $datum['id']);
            $key = $datum['sectionKey'] ?? '';
            $label = $key ? $name . ' (' . $key . ')' : $name;
            $optionData[$datum['id']] = $label;
        }

        return $optionData;
    }
}
