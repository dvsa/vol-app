<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Class TmApplicationOc
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TmApplicationOc extends AbstractData implements ListDataInterface
{
    protected $serviceName = 'TmApplicationOc';

    private $licenceOperatingCentreService;

    private $tmApplicationId;

    private $licenceId;

    /**
     * @param array $context
     * @param bool $useGroups
     * @return array
     */
    public function fetchListOptions($context, $useGroups = false)
    {
        $data = $this->fetchOperatingCentresData($context);

        if (!$data) {
            return [];
        }

        return $data;
    }

    /**
     * Ensures only a single call is made to the backend for each dataset
     *
     * @param $context
     * @return array
     */
    public function fetchOperatingCentresData($context)
    {
        if (is_null($this->getData('TmApplicationOc'))) {
            $bundle = [
                'children' => [
                    'transportManagerApplication',
                    'operatingCentre' => [
                        'children' => [
                            'address'
                        ]
                    ]
                ]
            ];
            $data = $this->getRestClient()->get(
                '',
                [
                    'limit' => 1000,
                    'transportManagerApplication' => $this->getTmApplicationId(),
                    'bundle' => json_encode($bundle)
                ]
            );
            $oc = [];

            if ($data['Count']) {
                foreach ($data['Results'] as $result) {
                    if ($result['transportManagerApplication']['action'] == 'A') {
                        $oc[$result['operatingCentre']['id']] =
                                $result['operatingCentre']['address']['addressLine1'] . ', ' .
                                $result['operatingCentre']['address']['addressLine2'] . ', ' .
                                $result['operatingCentre']['address']['town'];
                    }
                }
            }
            $licenceOcService = $this->getLicenceOperatingCentreService();
            $licenceOcData = $licenceOcService->getOperatingCentresForLicence($this->getLicenceId());
            if ($licenceOcData['Count']) {
                foreach ($licenceOcData['Results'] as $result) {
                    $oc[$result['operatingCentre']['id']] =
                            $result['operatingCentre']['address']['addressLine1'] . ', ' .
                            $result['operatingCentre']['address']['addressLine2'] . ', ' .
                            $result['operatingCentre']['address']['town'];
                }
            }
            $this->setData('TmApplicationOc', false);

            if (count($oc)) {
                $this->setData('TmApplicationOc', $oc);
            }
        }

        return $this->getData('TmApplicationOc');
    }

    /**
     * Set Licence Operating Centre Service
     *
     * @param Common\Service\Entity\LicenceOpratingCentreEntityService $service
     */
    public function setLicenceOperatingCentreService($service)
    {
        $this->licenceOperatingCentreService = $service;
    }

    /**
     * Get Licence Operating Centre Service
     *
     * @return Common\Service\Entity\LicenceOpratingCentreEntityService
     */
    public function getLicenceOperatingCentreService()
    {
        return $this->licenceOperatingCentreService;
    }

    /**
     * Set tmApplicationId
     *
     * @param int $tmAppId
     */
    public function setTmApplicationId($tmAppId)
    {
        $this->tmApplicationId = $tmAppId;
    }

    /**
     * Get tmApplicationId
     *
     * @return int
     */
    public function getTmApplicationId()
    {
        return $this->tmApplicationId;
    }

    /**
     * Set licenceId
     *
     * @param int $licenceId
     */
    public function setLicenceId($licenceId)
    {
        $this->licenceId = $licenceId;
    }

    /**
     * Get licenceId
     *
     * @return int
     */
    public function getLicenceId()
    {
        return $this->licenceId;
    }
}
