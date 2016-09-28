<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractDataService;
use Common\Service\Data\ListDataInterface;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\OperatingCentres as OperatingCentresQry;

/**
 * Operating Centres for Inspection Request data service
 *
 * @package Olcs\Service\Data
 */
class OperatingCentresForInspectionRequest extends AbstractDataService implements ListDataInterface
{
    /**
     * @var string
     */
    protected $type = 'licence';

    /**
     * @var int
     */
    protected $identifier;

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

        if (!empty($data['results'])) {
            foreach ($data['results'] as $oc) {
                $optionData[$oc['id']] =
                    $oc['address']['addressLine1'] . ', ' .
                    $oc['address']['addressLine2'] . ', ' .
                    $oc['address']['town'];
            }
        }

        return $optionData;
    }

    /**
     * Fetch list options
     *
     * @param array|string $context   Context
     * @param bool         $useGroups Use groups
     *
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
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
        if (is_null($this->getData('OperatingCentres'))) {

            $dtoData = OperatingCentresQry::create(
                [
                    'type'       => $this->getType(),
                    'identifier' => $this->getIdentifier()
                ]
            );
            $response = $this->handleQuery($dtoData);

            if (!$response->isOk()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
                return [];
            }

            $this->setData('OperatingCentres', $response->getResult());
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
     * @param string $type Type
     *
     * @return void
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
     * @param int $identifier Identifier
     *
     * @return void
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }
}
