<?php

/**
 * Operating Centres for Inspection Request data service
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Service\Data;

use Common\Service\Data\ListDataInterface;
use Common\Service\Data\AbstractData;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Dvsa\Olcs\Transfer\Query\InspectionRequest\OperatingCentres as OperatingCentresQry;

/**
 * Operating Centres for Inspection Request data service
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

    /**
     * Format data
     *
     * @param array $data
     * @return array
     */
    public function formatData(array $data)
    {
        $optionData = [];
        if (isset($data['results'])) {
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
        if (is_null($this->getData('OperatingCentres'))) {

            $params = [
                'type'       => $this->getType(),
                'identifier' => $this->getIdentifier()
            ];
            $queryToSend = $this->getServiceLocator()
                ->get('TransferAnnotationBuilder')
                ->createQuery(
                    OperatingCentresQry::create($params)
                );

            $response = $this->getServiceLocator()->get('QueryService')->send($queryToSend);

            if ($response->isClientError() || $response->isServerError()) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }

            $result = [];
            if ($response->isOk()) {
                $result = $response->getResult();
            }
            $this->setData('OperatingCentres', $result);
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
