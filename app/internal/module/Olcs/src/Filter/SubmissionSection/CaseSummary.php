<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;
use Zend\Filter\AbstractFilter;

/**
 * Class CaseSummary
 * @package Olcs\Filter\SubmissionSection
 */
class CaseSummary extends AbstractFilter
{
    /**
     * Filters data for case-summary section
     * @param array $data
     * @return array $filteredData
     */
    public function filter($data = array())
    {
        $vehiclesInPossession = $this->calculateVehiclesInPossession($data['licence']);
        $filteredData = array(
            'id' => $data['id'],
            'organisationName' => $data['licence']['organisation']['name'],
            'isMlh' => $data['licence']['organisation']['isMlh'],
            'organisationType' => $data['licence']['organisation']['type']['description'],
            'caseType' => isset($data['caseType']['id']) ? $data['caseType']['id'] : null,
            'ecmsNo' => $data['ecmsNo'],
            'licNo' => $data['licence']['licNo'],
            'licenceStartDate' => $data['licence']['inForceDate'],
            'licenceType' => $data['licence']['licenceType']['description'],
            'goodsOrPsv' => $data['licence']['goodsOrPsv']['description'],
            'serviceStandardDate' =>
                isset($data['application']['targetCompletionDate']) ?
                    $data['application']['targetCompletionDate'] : null,
            'licenceStatus' => $data['licence']['status']['description'],
            'totAuthorisedVehicles' => $data['licence']['totAuthVehicles'],
            'totAuthorisedTrailers' => $data['licence']['totAuthTrailers'],
            'vehiclesInPossession' => $vehiclesInPossession,
            'trailersInPossession' => $data['licence']['totAuthTrailers'],
            'businessType' =>
                isset($data['licence']['organisation']['natureOfBusinesss']) ?
                    $this->getNatureOfBusinessAsaString(
                        $data['licence']['organisation']['natureOfBusinesss']
                    )
                    : ''
        );

        return $filteredData;
    }

    /**
     * Get nature of business as a string
     * 
     * @params array $natureOfBusiness
     * @return string
     */
    protected function getNatureOfBusinessAsaString($natureOfBusiness = [])
    {
        $nob = [];
        foreach ($natureOfBusiness as $element) {
            $nob[] = $element['refData']['description'];
        }
        return implode(', ', $nob);
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
