<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Interfaces\ListData;
use Common\Service\Data\AbstractDataService;
use Dvsa\Olcs\Transfer\Query\IrhpPermitType\GetList;

/**
 * Class IrhpPermitType
 *
 * @package Common\Service\Data
 */
class IrhpPermitType extends AbstractDataService implements ListData
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
            $optionData[$datum['id']] = $datum['name']['description'];
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
        if (is_null($this->getData('IrhpPermitType'))) {
            $dtoData = GetList::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('IrhpPermitType', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('IrhpPermitType', $response->getResult()['results']);
            }
        }

        return $this->getData('IrhpPermitType');
    }
}
