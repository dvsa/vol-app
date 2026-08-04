<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\ApplicationPathGroupList;

/**
 * Class ApplicationPathGroup
 *
 *
 * @package Common\Service\Data
 */
class ApplicationPathGroup extends AbstractDataService implements ListData
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
            $optionData[$datum['id']] = $datum['name'];
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
        if (is_null($this->getData('ApplicationPathGroup'))) {
            $dtoData = ApplicationPathGroupList::create([]);
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('ApplicationPathGroup', false);
            if (isset($response->getResult()['results'])) {
                $this->setData('ApplicationPathGroup', $response->getResult()['results']);
            }
        }

        return $this->getData('ApplicationPathGroup');
    }
}
