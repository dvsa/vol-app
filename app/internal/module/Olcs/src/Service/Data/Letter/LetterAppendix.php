<?php

namespace Olcs\Service\Data\Letter;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Letter Appendix data service
 *
 * @package Olcs\Service\Data\Letter
 */
class LetterAppendix extends AbstractListDataService
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
        $data = (array)$this->getData('letter-appendix');

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
            TransferQry\Letter\LetterAppendix\GetList::create($params)
        );

        if (!$response->isOk()) {
            $body = $response->getBody();
            $errorMessage = 'Failed to fetch letter appendices: ' .
                ($body ? $body : 'HTTP ' . $response->getStatusCode());
            throw new DataServiceException($errorMessage);
        }

        $result = $response->getResult();

        $this->setData('letter-appendix', ($result['results'] ?? []));

        return $this->getData('letter-appendix');
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
            $name = $datum['name'] ?? ('Appendix #' . $datum['id']);
            $type = isset($datum['appendixType']) ? ' (' . ucfirst($datum['appendixType']) . ')' : '';
            $optionData[$datum['id']] = $name . $type;
        }

        return $optionData;
    }
}
