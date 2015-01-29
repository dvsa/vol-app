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
        $mostSeriousInfringement = $data['seriousInfringements'][0];

        $filteredData['overview'] = array(
            'id' => $mostSeriousInfringement['id'],
            'notificationNumber' => $mostSeriousInfringement['notificationNumber'],
            'siCategory' => $mostSeriousInfringement['siCategory']['description'],
            'siCategoryType' => $mostSeriousInfringement['siCategoryType']['description'],
            'infringementDate' => $mostSeriousInfringement['infringementDate'],
            'checkDate' => $mostSeriousInfringement['checkDate'],
            'isMemberState' => $mostSeriousInfringement['memberStateCode']['isMemberState']
        );

        return $filteredData;
    }
}
