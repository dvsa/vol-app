<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class TmPreviousHistory
 * @package Olcs\Filter\SubmissionSection
 */
class TmPreviousHistory extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for tm-previous-history section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        $dataToReturnArray = array('tables' => array('convictions-and-penalties' => [],
            'revoked-curtailed-suspended-licences' => []));
        if (isset($data['transportManager']['previousConvictions']) &&
            is_array($data['transportManager']['previousConvictions'])) {

            foreach ($data['transportManager']['previousConvictions'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['offence'] = $entity['categoryText'];
                $thisEntity['convictionDate'] = $entity['convictionDate'];
                $thisEntity['courtFpn'] = $entity['courtFpn'];
                $thisEntity['penalty'] = $entity['penalty'];

                $dataToReturnArray['tables']['convictions-and-penalties'][] = $thisEntity;
            }
        }

        if (isset($data['transportManager']['otherLicences']) &&
            is_array($data['transportManager']['otherLicences'])) {
            foreach ($data['transportManager']['otherLicences'] as $entity) {
                $thisEntity = array();
                $thisEntity['id'] = $entity['id'];
                $thisEntity['version'] = $entity['version'];
                $thisEntity['licNo'] = $entity['licNo'];
                $thisEntity['holderName'] = $entity['holderName'];

                $dataToReturnArray['tables']['revoked-curtailed-suspended-licences'][] = $thisEntity;
            }
        }

        return $dataToReturnArray;
    }
}
