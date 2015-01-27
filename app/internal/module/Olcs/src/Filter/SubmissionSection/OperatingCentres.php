<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class OperatingCentres
 * @package Olcs\Filter\SubmissionSection
 */
class OperatingCentres extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for conditions-and-undertakings section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = [];
        if (isset($data['licence']['operatingCentres']) && is_array($data['licence']['operatingCentres'])) {

            /*usort(
                $data['conditionUndertakings'],
                function ($a, $b) {
                    return strtotime($b['createdOn']) - strtotime($a['createdOn']);
                }
            );*/

            foreach ($data['licence']['operatingCentres'] as $entity) {
                $thisEntity = array();
                if (!(empty($entity['operatingCentre']))) {

                    $thisEntity['id'] = $entity['operatingCentre']['id'];
                    $thisEntity['version'] = $entity['operatingCentre']['version'];
                    $thisEntity['totAuthTrailers'] = $data['licence']['totAuthTrailers'];
                    $thisEntity['totAuthVehicles'] = $data['licence']['totAuthVehicles'];
                    if (empty($entity['operatingCentre']['address'])) {
                        $thisEntity['OcAddress'] = [];
                    } else {
                        $thisEntity['OcAddress'] = $entity['operatingCentre']['address'];
                    }
                    $dataToReturnArray['tables']['operating-centres'][] = $thisEntity;
                }
            }
        }

        return $dataToReturnArray;
    }
}
