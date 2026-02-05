<?php

namespace Olcs\Service\Data\Letter;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Letter Issue Type data service
 *
 * @package Olcs\Service\Data\Letter
 */
class LetterIssueType extends AbstractListDataService
{
    protected static $sort = 'displayOrder';
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
        $data = (array)$this->getData('letter-issue-types');

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
            TransferQry\Letter\LetterIssueType\GetList::create($params)
        );

        if (!$response->isOk()) {
            $body = $response->getBody();
            $errorMessage = 'Failed to fetch letter issue types: ' .
                ($body ?: 'HTTP ' . $response->getStatusCode());
            throw new DataServiceException($errorMessage);
        }

        $result = $response->getResult();

        $this->setData('letter-issue-types', ($result['results'] ?? []));

        return $this->getData('letter-issue-types');
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
