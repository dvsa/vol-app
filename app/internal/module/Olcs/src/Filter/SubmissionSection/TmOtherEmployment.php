<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TmOtherEmployment
 * @package Olcs\Filter\SubmissionSection
 */
class TmOtherEmployment extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for tm-other-employment section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array('tables' => array('tm-other-employment' => []));
        if (isset($data['transportManager']['employments']) && is_array($data['transportManager']['employments'])) {
            foreach ($data['transportManager']['employments'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['position'] = $entity['position'];
                $thisEntity['employerName'] = $entity['employerName'];
                $thisEntity['address'] =
                    $entity['contactDetails']['address'];
                $thisEntity['hoursPerWeek'] = $entity['hoursPerWeek'];

                $dataToReturnArray['tables']['tm-other-employment'][] = $thisEntity;
            }
        }

        return $dataToReturnArray;
    }
}
