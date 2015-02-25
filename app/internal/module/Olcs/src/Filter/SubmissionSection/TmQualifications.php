<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TmQualifications
 * @package Olcs\Filter\SubmissionSection
 */
class TmQualifications extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for tm-responsibilities section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array('tables' => array('tm-qualifications' => []));

        if (isset($data['transportManager']['qualifications']) &&
            is_array($data['transportManager']['qualifications'])) {
            foreach ($data['transportManager']['qualifications'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['qualificationType'] = $entity['qualificationType']['description'];
                $thisEntity['serialNo'] = $entity['serialNo'];
                $thisEntity['issuedDate'] = $entity['issuedDate'];
                $thisEntity['country'] = $entity['countryCode']['countryDesc'];

                $dataToReturnArray['tables']['tm-qualifications'][] = $thisEntity;
            }
        }

        return $dataToReturnArray;
    }
}
