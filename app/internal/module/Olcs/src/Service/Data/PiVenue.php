<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;
use Common\Service\Data\LicenceServiceTrait;

/**
 * Class PiVenue
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PiVenue extends AbstractData implements ListDataInterface
{
    use LicenceServiceTrait;

    protected $serviceName = 'PiVenue';

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
            $optionData[$datum['id']] = $datum['name'];
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
        $context = $this->getLicenceContext();

        $data = $this->fetchListData($context);

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
    public function fetchListData($params)
    {
        if (is_null($this->getData('PiVenue'))) {

            $data = $this->getRestClient()->get('', $params);

            $this->setData('PiVenue', false);

            if (isset($data['Results'])) {
                $this->setData('PiVenue', $data['Results']);
            }
        }

        return $this->getData('PiVenue');
    }
}
