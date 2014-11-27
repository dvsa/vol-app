<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;

/**
 * Class LinkedLicencesAppNumbers
 * @package Olcs\Filter\SubmissionSection
 */
class LinkedLicencesAppNumbers extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for linked-licences-app-numbers section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array();
        if (!empty($data['licence']['organisation']['licences']) &&
            count($data['licence']['organisation']['licences']) > 1) {
            foreach ($data['licence']['organisation']['licences'] as $licence) {
                $thisRow = array();
                if ($licence['id'] != $data['licence']['id']) {
                    $thisRow['id'] = $licence['id'];
                    $thisRow['version'] = $licence['version'];
                    $thisRow['licNo'] = $licence['licNo'];
                    $thisRow['status'] = $licence['status']['description'];
                    $thisRow['licenceType'] = $licence['licenceType']['description'];
                    $thisRow['totAuthTrailers'] = $licence['totAuthTrailers'];
                    $thisRow['totAuthVehicles'] = $licence['totAuthVehicles'];
                    $thisRow['vehiclesInPossession'] = $this->calculateVehiclesInPossession($licence);
                    $thisRow['trailersInPossession'] = $this->calculateTrailersInPossession($data['licence']);
                    $dataToReturnArray[] = $thisRow;
                }
            }
        }
        return $dataToReturnArray;
    }
}
