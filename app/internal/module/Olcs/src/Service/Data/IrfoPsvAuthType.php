<?php

namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataTrait;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoPsvAuthTypeList;
use Common\Service\Entity\Exceptions\UnexpectedResponseException;

/**
 * Class IrfoPsvAuthType
 */
class IrfoPsvAuthType extends AbstractDataService implements ListDataInterface
{
    /**
     * Format data!
     *
     * @param array $data
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
     * @param $category
     * @param bool $useGroups
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
     * Ensures only a single call is made to the backend for each dataset
     *
     * @return array
     */
    public function fetchListData()
    {
        if (is_null($this->getData('IrfoPsvAuthType'))) {

            $dtoData = IrfoPsvAuthTypeList::create([]);
            $response = $this->handleQuery($dtoData);
            if (!$response->isOk()) {
                throw new UnexpectedResponseException('unknown-error');
            }

            $this->setData('IrfoPsvAuthType', false);
            if (isset($response->getResult()['results'])) {
                $this->setData('IrfoPsvAuthType', $response->getResult()['results']);
            }
        }

        return $this->getData('IrfoPsvAuthType');

    }
}
