<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class OutstandingApplications
 * @package Olcs\Filter\SubmissionSection
 */
class OutstandingApplications extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for outstanding-applications section
     * @param array $data
     * @return array $data
     */
    public function filter($data = array())
    {
        $filteredData = array();
        $dataToReturnArray = [];

        $dateUtilityService = $this->getServiceLocator()->getServiceLocator()->get('Olcs\Service\Utility\DateUtility');

        if (isset($data['licence']['organisation']['licences'])) {
            foreach ($data['licence']['organisation']['licences'] as $licence) {
                foreach ($licence['applications'] as $application) {
                    $thisData = array();
                    $thisData['id'] = $application['id'];
                    $thisData['version'] = $application['version'];
                    $thisData['applicationType'] = 'TBC';
                    $thisData['receivedDate'] = $application['receivedDate'];

                    $thisData['oor'] = $dateUtilityService->calculateOor($application);
                    $thisData['ooo'] = $dateUtilityService->calculateOoo($application);

                    $dataToReturnArray[] = $thisData;
                }
            }
        }

        $filteredData['tables']['outstanding-applications'] = $dataToReturnArray;

        return $filteredData;
    }
}
