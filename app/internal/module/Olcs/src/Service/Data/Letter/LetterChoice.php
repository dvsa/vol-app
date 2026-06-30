<?php

namespace Olcs\Service\Data\Letter;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractListDataService;
use Dvsa\Olcs\Transfer\Query as TransferQry;

/**
 * Letter Choice data service
 */
class LetterChoice extends AbstractListDataService
{
    protected static $sort = 'label';
    protected static $order = 'ASC';

    #[\Override]
    public function fetchListData($context = null)
    {
        $data = (array)$this->getData('letter-choice');

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
            TransferQry\Letter\LetterChoice\GetList::create($params)
        );

        if (!$response->isOk()) {
            throw new DataServiceException('Failed to fetch letter choices');
        }

        $result = $response->getResult();

        $this->setData('letter-choice', ($result['results'] ?? []));

        return $this->getData('letter-choice');
    }

    #[\Override]
    public function formatData(array $data)
    {
        $optionData = [];

        foreach ($data as $datum) {
            if (!empty($datum['isActive'])) {
                $optionData[$datum['id']] = $datum['label'] ?? ('Choice #' . $datum['id']);
            }
        }

        return $optionData;
    }
}
