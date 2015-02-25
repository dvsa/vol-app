<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TmResponsibilities
 * @package Olcs\Filter\SubmissionSection
 */
class TmResponsibilities extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for tm-responsibilities section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array('tables' => array('applications' => [], 'licences' => []));
        if (isset($data['transportManager']['tmApplications']) &&
            is_array($data['transportManager']['tmApplications'])) {

            foreach ($data['transportManager']['tmApplications'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['managerType'] = $data['transportManager']['tmType']['description'];
                $thisEntity['noOpCentres'] = count($entity['operatingCentres']);
                $thisEntity['applicationId'] = isset($entity['application']['id']) ? $entity['application']['id'] : '';
                $thisEntity['organisationName'] = isset($entity['application']['licence']['organisation']['name']) ?
                    $entity['application']['licence']['organisation']['name'] : '';
                $thisEntity['hrsPerWeek'] = $this->totalWeeklyHours($entity);

                $dataToReturnArray['tables']['applications'][] = $thisEntity;
            }
        }

        if (isset($data['transportManager']['tmLicences']) &&
            is_array($data['transportManager']['tmLicences'])) {
            foreach ($data['transportManager']['tmLicences'] as $entity) {

                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['managerType'] = $data['transportManager']['tmType']['description'];
                $thisEntity['noOpCentres'] = count($entity['operatingCentres']);
                $thisEntity['licNo'] = isset($entity['licence']) ? $entity['licence']['licNo'] : '';
                $thisEntity['organisationName'] = isset($entity['licence']['organisation']) ?
                    $entity['licence']['organisation']['name'] : '';
                $thisEntity['hrsPerWeek'] = $this->totalWeeklyHours($entity);

                $dataToReturnArray['tables']['licences'][] = $thisEntity;
            }
        }

        return $dataToReturnArray;
    }

    /**
     * Method to total the days hours from entity. Returns a final total for the week.
     *
     * @param $entity
     * @return int
     */
    private function totalWeeklyHours($entity)
    {
        $weeklyHours = 0;
        $daysOfWeek = array('Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun');
        foreach ($daysOfWeek as $day) {
            if (isset($entity['hours' . $day]) && !empty($entity['hours' . $day])) {
                $weeklyHours += $entity['hours' . $day];
            }
        }
        return $weeklyHours;
    }
}
