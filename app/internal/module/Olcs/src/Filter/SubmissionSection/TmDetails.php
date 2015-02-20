<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TmDetails
 * @package Olcs\Filter\SubmissionSection
 */
class TmDetails extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for tm-details section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $filteredData = array();
        if (isset($data['transportManager']) && is_array($data['transportManager'])) {
            $tmData = $data['transportManager'];

            $filteredData['overview'] = array(
                'id' => isset($tmData['id']) ? $data['id'] : '',
                'title' => isset($tmData['homeCd']['person']['title']) ? $tmData['homeCd']['person']['title'] : '',
                'forename' => isset($tmData['homeCd']['person']['forename']) ?
                        $tmData['homeCd']['person']['forename'] : '',
                'familyName' => isset($tmData['homeCd']['person']['familyName']) ?
                        $tmData['homeCd']['person']['familyName'] : '',
                'emailAddress' => isset($tmData['homeCd']['emailAddress']) ? $tmData['homeCd']['emailAddress'] : '',
                'dob' =>  isset($tmData['homeCd']['person']['birthDate']) ? $tmData['homeCd']['person']['birthDate']
                        : '',
                'placeOfBirth' =>  isset($tmData['homeCd']['person']['birthPlace']) ?
                        $tmData['homeCd']['person']['birthPlace'] : '',
                'tmType' => isset($tmData['tmType']['description']) ? $tmData['tmType']['description'] : '',
                'homeAddress' => isset($tmData['homeCd']['address']) ? $tmData['homeCd']['address'] : '',
                'workAddress' => isset($tmData['workCd']['address']) ? $tmData['workCd']['address'] : '',
            );
        }
        return $filteredData;
    }
}
