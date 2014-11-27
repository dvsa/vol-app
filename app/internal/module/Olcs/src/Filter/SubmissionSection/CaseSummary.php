<?php

namespace Olcs\Filter\SubmissionSection;

use Common\Exception\ResourceNotFoundException;

/**
 * Class CaseSummary
 * @package Olcs\Filter\SubmissionSection
 */
class CaseSummary extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for case-summary section
     * @param array $data
     * @return array $filteredData
     */
    public function filter($data = array())
    {
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
            'vehiclesInPossession' => $this->calculateVehiclesInPossession($data['licence']),
            'trailersInPossession' =>  $this->calculateTrailersInPossession($data['licence']),
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
}
