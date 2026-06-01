<?php

namespace Olcs\Service\Data\Letter;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Letter Todo data service
 *
 * Populates the "Linked To-dos" multi-select on the Letter Issue admin form.
 *
 * @package Olcs\Service\Data\Letter
 */
class LetterTodo extends AbstractListDataService
{
    protected static $sort = 'todoKey';
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
        $data = (array)$this->getData('letter-todos');

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
            TransferQry\Letter\LetterTodo\GetList::create($params)
        );

        if (!$response->isOk()) {
            $body = $response->getBody();
            $errorMessage = 'Failed to fetch letter todos: ' .
                ($body ?: 'HTTP ' . $response->getStatusCode());
            throw new DataServiceException($errorMessage);
        }

        $result = $response->getResult();

        $this->setData('letter-todos', ($result['results'] ?? []));

        return $this->getData('letter-todos');
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
            $optionData[$datum['id']] = $datum['todoKey'] ?? ('Todo #' . $datum['id']);
        }

        return $optionData;
    }
}
