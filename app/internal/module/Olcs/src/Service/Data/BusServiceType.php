<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Class BusServiceType
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusServiceType extends AbstractData implements ListDataInterface
{
    protected $serviceName = 'BusServiceType';

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
        if (is_null($this->getData('BusServiceType'))) {

            $data = $this->getRestClient()->get('', ['limit' => 1000]);

            $this->setData('BusServiceType', false);

            if (isset($data['Results'])) {
                $this->setData('BusServiceType', $data['Results']);
            }
        }

        return $this->getData('BusServiceType');
    }
}
