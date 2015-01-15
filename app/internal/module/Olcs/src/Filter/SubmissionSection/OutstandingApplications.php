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

        foreach ($data['licence']['applications'] as $application) {
            $thisData = array();
            $thisData['id'] = $application['id'];
            $thisData['version'] = $application['version'];
            $thisData['applicationType'] = $application['goodsOrPsv']['description'];
            $thisData['receivedDate'] = $application['receivedDate'];
            $thisData['oooood'] = $this->calculateOorOod($application);

            $dataToReturnArray[] = $thisData;
        }

        $filteredData['tables']['outstanding-applications'] = $dataToReturnArray;
        return $filteredData;
    }
}
