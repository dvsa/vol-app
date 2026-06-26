<?php

declare(strict_types=1);

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Dvsa\Olcs\Transfer\Query as TransferQry;

class MessagingSubject extends AbstractListDataService
{
    public const SORT_BY = 'description';

    public const SORT_ORDER = 'ASC';

    public const ONLY_ACTIVE = true;

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
        $data = (array)$this->getData('subjects');

        if ($data !== []) {
            return $data;
        }

        $response = $this->handleQuery(
            TransferQry\Messaging\Subjects\All::create([
                'sort' => static::SORT_BY,
                'order' => static::SORT_ORDER,
                'onlyActive' => static::ONLY_ACTIVE,
            ])
        );

        if (!$response->isOk()) {
            throw new DataServiceException('unknown-error');
        }

        $result = $response->getResult();

        $this->setData('subjects', ($result['results'] ?? null));

        return $this->getData('subjects');
    }
}
