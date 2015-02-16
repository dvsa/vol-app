<?php

namespace Olcs\Service\Data;

use Common\Service\Data\AbstractData;
use Common\Service\Data\ListDataInterface;

/**
 * Class TmApplicationOc
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ApplicationOperatingCentre extends AbstractData implements ListDataInterface
{
    protected $serviceName = 'ApplicationOperatingCentre';

    private $licenceOperatingCentreService;

    private $applicationId;

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
        if (is_null($this->getData('applicationOc'))) {
            $bundle = [
                'children' => [
                    'application',
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
                    'application' => $this->getApplicationId(),
                    'bundle' => json_encode($bundle)
                ]
            );
            $oc = [];
            $deleted = [];
            if ($data['Count']) {
                foreach ($data['Results'] as $result) {
                    if ($result['action'] !== 'D') {
                        $oc[$result['operatingCentre']['id']] =
                                $result['operatingCentre']['address']['addressLine1'] . ', ' .
                                $result['operatingCentre']['address']['addressLine2'] . ', ' .
                                $result['operatingCentre']['address']['town'];
                    } else {
                        $deleted[] = $result['operatingCentre']['id'];
                    }
                }
            }
            $licenceOcService = $this->getLicenceOperatingCentreService();
            $licenceOcData = $licenceOcService->getOperatingCentresForLicence($this->getLicenceId());
            if ($licenceOcData['Count']) {
                foreach ($licenceOcData['Results'] as $result) {
                    if (array_search($result['operatingCentre']['id'], $deleted) === false) {
                        $oc[$result['operatingCentre']['id']] =
                                $result['operatingCentre']['address']['addressLine1'] . ', ' .
                                $result['operatingCentre']['address']['addressLine2'] . ', ' .
                                $result['operatingCentre']['address']['town'];
                    }
                }
            }
            $this->setData('applicationOc', false);

            if (count($oc)) {
                $this->setData('applicationOc', $oc);
            }
        }

        return $this->getData('applicationOc');
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
     * Set applicationId
     *
     * @param int $appId
     */
    public function setApplicationId($appId)
    {
        $this->applicationId = $appId;
    }

    /**
     * Get tmApplicationId
     *
     * @return int
     */
    public function getApplicationId()
    {
        return $this->applicationId;
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
