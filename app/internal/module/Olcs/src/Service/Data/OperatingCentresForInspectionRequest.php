<?php

/**
 * Inspection Request Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Inspection Request Trait
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentresForInspectionRequest extends AbstractData implements
    ListDataInterface,
    ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    protected $serviceName = 'LicenceOperatingCentre';

    protected $type = 'licence';

    protected $identifier;

    protected $formatted = false;

    /**
     * Format data
     *
     * @param array $data
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];
        foreach ($data as $datum) {
            if (isset($datum['operatingCentre'])) {
                $optionData[$datum['operatingCentre']['id']] =
                    $datum['operatingCentre']['address']['addressLine1'] . ', ' .
                    $datum['operatingCentre']['address']['addressLine2'] . ', ' .
                    $datum['operatingCentre']['address']['town'];
            }
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

        return !$this->formatted ? $this->formatData($data) : $data;
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @return array
     */
    public function fetchListData()
    {
        if (is_null($this->getData('OperatingCentres'))) {

            if ($this->getType() == 'application') {
                $data = $this->getServiceLocator()
                    ->get('Entity\ApplicationOperatingCentre')
                    ->getForSelect($this->getIdentifier());

                $this->formatted = true;
            } else {
                $dataFetched = $this->getServiceLocator()
                    ->get('Entity\LicenceOperatingCentre')
                    ->getAllForInspectionRequest($this->getIdentifier());
                $data = isset($dataFetched['Results']) ? $dataFetched['Results'] : null;
            }
            $this->setData('OperatingCentres', $data);
        }
        return $this->getData('OperatingCentres');
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * Get indentifier
     *
     * @return int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Set identifier
     *
     * @param int $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
}
