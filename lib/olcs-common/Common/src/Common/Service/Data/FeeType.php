<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\FeeType\GetDistinctList;

/**
 * Class FeeType
 *
 * @package Common\Service\Data
 */
class FeeType extends AbstractDataService implements ListData
{
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
            $optionData[$datum['id']] = $datum['id'];
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param string $category Category
     * @param bool $useGroups Use groups
     *
     * @return array
     * @throws DataServiceException
     */
    #[\Override]
    public function fetchListOptions($category, $useGroups = false)
    {
        $data = $this->fetchListData();

        if (!$data) {
            return [];
        }

        return $this->formatData($data);
    }

    /**
     * Fetch list data
     *
     * @return array
     * @throws DataServiceException
     */
    public function fetchListData()
    {
        if (is_null($this->getData('FeeType'))) {
            $dtoData = GetDistinctList::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('FeeType', false);
            if (isset($response->getResult()['results'])) {
                $this->setData('FeeType', $response->getResult()['results']);
            }
        }

        return $this->getData('FeeType');
    }
}
