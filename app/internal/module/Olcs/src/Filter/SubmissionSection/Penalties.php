<?php

namespace Olcs\Filter\SubmissionSection;

/**
 * Class Penalties
 * @package Olcs\Filter\SubmissionSection
 */
class Penalties extends AbstractSubmissionSectionFilter
{
    /**
     * Filters data for case-outline section
     * @param array $data
     * @return array
     */
    public function filter($data = array())
    {
        $dataToReturnArray = [];
        $seriousInfringement = isset($data['seriousInfringements'][0]) ? $data['seriousInfringements'][0] : [];

        $dataToReturnArray['overview']['vrm'] = isset($data['erruVrm']) ? $data['erruVrm'] : '';
        $dataToReturnArray['overview']['infringementId'] = isset($seriousInfringement['id']) ?
            $seriousInfringement['id'] : '';
        $dataToReturnArray['overview']['notificationNumber'] = isset($seriousInfringement['notificationNumber']) ?
            $seriousInfringement['notificationNumber'] : '';
        $dataToReturnArray['overview']['infringementDate'] = isset($seriousInfringement['infringementDate']) ?
            $seriousInfringement['infringementDate'] : '';
        $dataToReturnArray['overview']['checkDate'] = isset($seriousInfringement['checkDate']) ? $seriousInfringement['checkDate'] : '';
        $dataToReturnArray['overview']['category'] = isset($seriousInfringement['siCategory']['description']) ?
            $seriousInfringement['siCategory']['description'] : '';
        $dataToReturnArray['overview']['categoryType'] = isset($seriousInfringement['siCategoryType']['description']) ?
            $seriousInfringement['siCategoryType']['description'] : '';
        $dataToReturnArray['overview']['transportUndertakingName'] = isset
        ($data['erruTransportUndertakingName']) ? $data['erruTransportUndertakingName'] : '';
        $dataToReturnArray['overview']['memberState'] = isset($seriousInfringement['memberStateCode']['countryDesc']) ?
            $seriousInfringement['memberStateCode']['countryDesc'] : '';
        $dataToReturnArray['overview']['originatingAuthority'] = isset($data['erruOriginatingAuthority']) ?
            $data['erruOriginatingAuthority'] : '';

        $dataToReturnArray['tables']['applied-penalties'] = [];
        $dataToReturnArray['tables']['imposed-penalties'] = [];
        $dataToReturnArray['tables']['requested-penalties'] = [];

        if (isset($seriousInfringement['appliedPenalties'])) {
            foreach ($seriousInfringement['appliedPenalties'] as $appliedPenalty) {
                $thisAppliedPenalty = array();
                $thisAppliedPenalty['id'] = $appliedPenalty['id'];
                $thisAppliedPenalty['version'] = $appliedPenalty['version'];
                $thisAppliedPenalty['penaltyType'] = $appliedPenalty['siPenaltyType']['description'];
                $thisAppliedPenalty['startDate'] = $appliedPenalty['startDate'];
                $thisAppliedPenalty['endDate'] = $appliedPenalty['endDate'];
                $thisAppliedPenalty['imposed'] = $appliedPenalty['imposed'];
                $dataToReturnArray['tables']['applied-penalties'][] = $thisAppliedPenalty;
            }
        }
        if (isset($seriousInfringement['imposedErrus'])) {
            foreach ($seriousInfringement['imposedErrus'] as $imposedPenalty) {
                $thisImposedPenalty = array();
                $thisImposedPenalty['id'] = $imposedPenalty['id'];
                $thisImposedPenalty['version'] = $imposedPenalty['version'];
                $thisImposedPenalty['finalDecisionDate'] = $imposedPenalty['finalDecisionDate'];
                $thisImposedPenalty['penaltyType'] = $imposedPenalty['siPenaltyImposedType']['description'];
                $thisImposedPenalty['startDate'] = $imposedPenalty['startDate'];
                $thisImposedPenalty['endDate'] = $imposedPenalty['endDate'];
                $thisImposedPenalty['executed'] = $imposedPenalty['executed'];
                $dataToReturnArray['tables']['imposed-penalties'][] = $thisImposedPenalty;
            }
        }
        if (isset($seriousInfringement['requestedErrus'])) {
            foreach ($seriousInfringement['requestedErrus'] as $requestedPenalty) {
                $thisRequestedPenalty = array();
                $thisRequestedPenalty['id'] = $requestedPenalty['id'];
                $thisRequestedPenalty['version'] = $requestedPenalty['version'];
                $thisRequestedPenalty['penaltyType'] = $requestedPenalty['siPenaltyRequestedType']['description'];
                $thisRequestedPenalty['duration'] = $requestedPenalty['duration'];
                $dataToReturnArray['tables']['requested-penalties'][] = $thisRequestedPenalty;
            }
        }
        $dataToReturnArray['text'] = isset($data['penaltiesNote']) ? $data['penaltiesNote'] : '';
        return $dataToReturnArray;
    }
}
