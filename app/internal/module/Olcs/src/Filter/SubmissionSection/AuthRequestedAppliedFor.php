<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class AuthRequestedAppliedFor
 * @package Olcs\Filter\SubmissionSection
 */
class AuthRequestedAppliedFor extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for auth-requested-applied-for section
     * @param array $data
     * @return array $data
     */
    public function filter($data = array())
    {
        $filteredData = array();
        $dataToReturnArray = [];

        foreach ($data['licence']['applications'] as $application) {
            $thisData = array();
            $thisData['id'] = $application['id'];
            $thisData['version'] = $application['version'];

            $thisData['currentVehiclesInPossession'] = '0';
            $thisData['currentTrailersInPossession'] = '0';
            $thisData['currentVehicleAuthorisation'] = '0';
            $thisData['currentTrailerAuthorisation'] = '0';

            if ($application['isVariation']) {
                $vip = $this->calculateVehiclesInPossession($data['licence']);
                $tip = $this->calculateTrailersInPossession($data['licence']);
                $thisData['currentVehiclesInPossession'] = $vip;
                $thisData['currentTrailersInPossession'] = $tip;

                $thisData['currentVehicleAuthorisation'] =
                    !empty($data['licence']['totAuthVehicles']) ? $data['licence']['totAuthVehicles'] : '0';
                $thisData['currentTrailerAuthorisation'] =
                    !empty($data['licence']['totAuthTrailers']) ? $data['licence']['totAuthTrailers'] : '0';
            }

            $thisData['requestedVehicleAuthorisation'] =
                !empty($application['totAuthVehicles']) ? $application['totAuthVehicles'] : '0';
            $thisData['requestedTrailerAuthorisation'] =
                !empty($application['totAuthTrailers']) ? $application['totAuthTrailers'] : '0';
            $dataToReturnArray[] = $thisData;
        }

        $filteredData['tables']['auth-requested-applied-for'] = $dataToReturnArray;
        return $filteredData;
    }
}
