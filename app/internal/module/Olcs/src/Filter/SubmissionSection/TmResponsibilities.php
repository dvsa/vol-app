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
        $dataToReturnArray = array('tables' => array('tm-details' => []));
        //var_dump($data);exit;
        if (isset($data['statements']) && is_array($data['statements'])) {
            foreach ($data['statements'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['requestedDate'] = $entity['requestedDate'];
                $thisEntity['requestedBy']['title'] =
                    $entity['requestorsContactDetails']['person']['title'];
                $thisEntity['requestedBy']['forename'] =
                    $entity['requestorsContactDetails']['person']['forename'];
                $thisEntity['requestedBy']['familyName'] =
                    $entity['requestorsContactDetails']['person']['familyName'];
                $thisEntity['statementType'] = $entity['statementType']['description'];
                $thisEntity['stoppedDate'] = $entity['stoppedDate'];
                $thisEntity['requestorsBody'] = $entity['requestorsBody'];
                $thisEntity['issuedDate'] = $entity['issuedDate'];
                $thisEntity['vrm'] = $entity['vrm'];

                $dataToReturnArray['tables']['statements'][] = $thisEntity;
            }
        }

        return $dataToReturnArray;
    }
}
