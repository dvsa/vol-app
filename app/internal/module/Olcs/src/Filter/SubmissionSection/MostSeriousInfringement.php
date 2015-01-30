<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class MostSeriousInfringment
 * @package Olcs\Filter\SubmissionSection
 */
class MostSeriousInfringement extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for most-serious-infringement section
     * @param array $data
     * @return array $filteredData
     */
    public function filter($data = array())
    {
        $filteredData = array();

        if (isset($data['seriousInfringements'][0])) {
            $mostSeriousInfringement = $data['seriousInfringements'][0];

            $filteredData['id'] = isset($mostSeriousInfringement['id']) ? $mostSeriousInfringement['id'] : '';
            $filteredData['notificationNumber'] = isset($mostSeriousInfringement['notificationNumber']) ?
                $mostSeriousInfringement['notificationNumber'] : '';
            $filteredData['siCategory'] = isset($mostSeriousInfringement['siCategory']['description']) ?
                $mostSeriousInfringement['siCategory']['description'] : '';
            $filteredData['siCategoryType'] = isset($mostSeriousInfringement['siCategoryType']['description']) ?
                $mostSeriousInfringement['siCategoryType']['description'] : '';
            $filteredData['infringementDate'] = isset($mostSeriousInfringement['infringementDate']) ?
                $mostSeriousInfringement['infringementDate'] : '';
            $filteredData['checkDate'] = isset($mostSeriousInfringement['checkDate']) ?
                $mostSeriousInfringement['checkDate'] : '';
            $filteredData['isMemberState'] = isset($mostSeriousInfringement['memberStateCode']['isMemberState']) ?
                $mostSeriousInfringement['memberStateCode']['isMemberState'] : '';
        }
        return ['overview' => $filteredData];
    }
}
