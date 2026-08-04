<?php

namespace Common\Service\Data;

use Common\Exception\DataServiceException;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\Interfaces\ListData;
use Dvsa\Olcs\Transfer\Query\Si\SiCategoryTypeListData;

/**
 * Class SiCategoryType
 */
class SiCategoryType extends AbstractDataService implements ListData
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
            $optionData[$datum['id']] = $datum['description'];
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param string $category  Category
     * @param bool   $useGroups Use groups
     *
     * @return array
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
     */
    public function fetchListData()
    {
        if (is_null($this->getData('SiCategoryType'))) {
            $dtoData = SiCategoryTypeListData::create(
                [
                    'sort'  => 'description',
                    'order' => 'ASC',
                ]
            );

            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                throw new DataServiceException('unknown-error');
            }

            $this->setData('SiCategoryType', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('SiCategoryType', $response->getResult()['results']);
            }
        }

        return $this->getData('SiCategoryType');
    }
}
