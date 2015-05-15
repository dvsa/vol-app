<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Class IrfoPsvAuthType
 */
class IrfoPsvAuthType extends AbstractData implements ListDataInterface
{
    protected $serviceName = 'IrfoPsvAuthType';

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
     * @internal param $category
     * @return array
     */
    public function fetchListData()
    {
        if (is_null($this->getData('IrfoPsvAuthType'))) {

            $data = $this->getRestClient()->get('', ['limit' => 1000]);

            $this->setData('IrfoPsvAuthType', false);

            if (isset($data['Results'])) {
                $this->setData('IrfoPsvAuthType', $data['Results']);
            }
        }

        return $this->getData('IrfoPsvAuthType');
    }
}
