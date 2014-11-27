<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;
use Zend\Filter\AbstractFilter;

/**
 * Class LinkedLicencesAppNumbers
 * @package Olcs\Filter\SubmissionSection
 */
class LinkedLicencesAppNumbers extends AbstractFilter
{
    /**
     * Filters data for linked-licences-app-numbers section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array();
        if (!empty($data['licence']['organisation']['licences'])) {
            foreach ($data['licence']['organisation']['licences'] as $licence) {
                $thisRow = array();
                $thisRow['id'] = $licence['id'];
                $thisRow['version'] = $licence['version'];
                $thisRow['licNo'] = $licence['licNo'];
                $thisRow['status'] = $licence['status']['description'];
                $thisRow['licenceType'] = $licence['licenceType']['description'];
                $thisRow['totAuthTrailers'] = $licence['totAuthTrailers'];
                $thisRow['totAuthVehicles'] = $licence['totAuthVehicles'];
                $thisRow['vehiclesInPosession'] = $this->calculateVehiclesInPossession($licence);
                $thisRow['trailersInPossession'] = 0;

                $dataToReturnArray[] = $thisRow;
            }
        }
        return $dataToReturnArray;
    }

    /**
     * Calculates the vehicles in possession.
     *
     * @param array $licenceData
     * @return int
     */
    private function calculateVehiclesInPossession($licenceData)
    {
        $vehiclesInPossession = 0;
        if (isset($licenceData['licenceVehicles']) && is_array($licenceData['licenceVehicles'])) {
            foreach ($licenceData['licenceVehicles'] as $vehicle) {
                if (!empty($vehicle['specifiedDate']) && empty($vehicle['deletedDate'])) {
                    $vehiclesInPossession++;
                }
            }
        }
        return $vehiclesInPossession;
    }
}
