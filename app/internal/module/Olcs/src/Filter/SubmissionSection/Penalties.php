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
        $data = $data[0];
        $dataToReturnArray = [];

        $dataToReturnArray['overview']['vrm'] = $data['case']['erruVrm'];
        $dataToReturnArray['overview']['infringementId'] = $data['id'];
        $dataToReturnArray['overview']['notificationNumber'] = $data['notificationNumber'];
        $dataToReturnArray['overview']['infringementDate'] = $data['infringementDate'];
        $dataToReturnArray['overview']['checkDate'] = $data['checkDate'];
        $dataToReturnArray['overview']['category'] = $data['siCategory']['description'];
        $dataToReturnArray['overview']['categoryType'] = $data['siCategoryType']['description'];
        $dataToReturnArray['overview']['transportUndertakingName'] = $data['case']['erruTransportUndertakingName'];
        $dataToReturnArray['overview']['memberState'] = $data['memberStateCode']['countryDesc'];
        $dataToReturnArray['overview']['originatingAuthority'] = $data['case']['erruOriginatingAuthority'];

        $dataToReturnArray['tables']['applied-penalties'] = [];
        $dataToReturnArray['tables']['imposed-penalties'] = [];
        $dataToReturnArray['tables']['requested-penalties'] = [];

        if (isset($data['appliedPenalties'])) {
            foreach ($data['appliedPenalties'] as $appliedPenalty) {
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
        if (isset($data['imposedErrus'])) {
            foreach ($data['imposedErrus'] as $imposedPenalty) {
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
        if (isset($data['requestedErrus'])) {
            foreach ($data['requestedErrus'] as $requestedPenalty) {
                $thisRequestedPenalty = array();
                $thisRequestedPenalty['id'] = $requestedPenalty['id'];
                $thisRequestedPenalty['version'] = $requestedPenalty['version'];
                $thisRequestedPenalty['penaltyType'] = $requestedPenalty['siPenaltyRequestedType']['description'];
                $thisRequestedPenalty['duration'] = $requestedPenalty['duration'];
                $dataToReturnArray['tables']['requested-penalties'][] = $thisRequestedPenalty;
            }
        }
        $dataToReturnArray['text'] = $data['case']['penaltiesNote'];

        return $dataToReturnArray;
    }
}
