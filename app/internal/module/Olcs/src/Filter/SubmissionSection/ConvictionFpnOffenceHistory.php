<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class ConvictionFpnOffenceHistory
 * @package Olcs\Filter\SubmissionSection
 */
class ConvictionFpnOffenceHistory extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for conviction-fpn-offence-history section
     * @param array $data
     * @return array $dataToReturnArray
     */
    public function filter($data = array())
    {
        if (isset($data['convictions'])) {
            usort(
                $data['convictions'],
                function ($a, $b) {
                    return strtotime($b['convictionDate']) - strtotime($a['convictionDate']);
                }
            );

            $dataToReturnArray = array();

            foreach ($data['convictions'] as $conviction) {
                $thisConviction = array();
                $thisConviction['id'] = $conviction['id'];
                $thisConviction['offenceDate'] = $conviction['offenceDate'];
                $thisConviction['convictionDate'] = $conviction['convictionDate'];
                $thisConviction['defendantType'] = $conviction['defendantType'];

                if ($conviction['defendantType']['id'] == 'def_t_op') {
                    $thisConviction['name'] = $conviction['operatorName'];
                } else {
                    $thisConviction['name'] = $conviction['personFirstname'] . ' ' . $conviction['personLastname'];
                }

                $thisConviction['categoryText'] = $conviction['categoryText'];
                $thisConviction['court'] = $conviction['court'];
                $thisConviction['penalty'] = $conviction['penalty'];
                $thisConviction['msi'] = $conviction['msi'];
                $thisConviction['isDeclared'] = !empty($conviction['isDeclared']) ?
                    $conviction['isDeclared'] : 'N';
                $thisConviction['isDealtWith'] = !empty($conviction['isDealtWith']) ?
                    $conviction['isDealtWith'] : 'N';
                $dataToReturnArray[] = $thisConviction;
            }
        }
        return $dataToReturnArray;
    }
}
