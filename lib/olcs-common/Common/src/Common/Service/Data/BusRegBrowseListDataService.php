<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseContextList;

/**
 * BusRegBrowse List data service.
 */
class BusRegBrowseListDataService extends AbstractDataService implements ListData
{
    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    #[\Override]
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchListData($context);

        if (!$data) {
            return [];
        }

        return $this->formatData($data, $context);
    }

    /**
     * Format data
     *
     * @param array  $data    Data
     * @param string $context Context
     *
     * @return array
     */
    public function formatData(array $data, $context)
    {
        $optionData = [];

        foreach ($data as $datum) {
            $optionData[$datum[$context]] = $datum[$context];
        }

        return $optionData;
    }

    /**
     * Fetch list data
     *
     * @param string $context Context
     *
     * @return array
     * @throw DataServiceException
     */
    public function fetchListData($context)
    {
        $cacheId = 'BusRegBrowse' . ucfirst($context);

        if ($this->getData($cacheId) === null) {
            $dtoData = BusRegBrowseContextList::create(
                [
                    'context' => $context,
                    'sort' => $context,
                    'order' => 'ASC'
                ]
            );

            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData($cacheId, false);
            $result = $response->getResult();

            if (isset($result['result'])) {
                $this->setData($cacheId, $result['result']);
            }
        }

        return $this->getData($cacheId);
    }
}
