<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;
use Dvsa\Olcs\Transfer\Query\Si\SiPenaltyTypeListData;

/**
 * Class SiPenaltyType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class SiPenaltyType extends AbstractDataService implements ListDataInterface
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
            $optionData[$datum['id']] = $datum['id'] . ' - ' . $datum['description'];
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
        if (is_null($this->getData('SiPenaltyType'))) {
            $response = $this->handleQuery(
                SiPenaltyTypeListData::create([])
            );

            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('SiPenaltyType', false);

            if (isset($response->getResult()['results'])) {
                $this->setData('SiPenaltyType', $response->getResult()['results']);
            }
        }

        return $this->getData('SiPenaltyType');
    }
}
