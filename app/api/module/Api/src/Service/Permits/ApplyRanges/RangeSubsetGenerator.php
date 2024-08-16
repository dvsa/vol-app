<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;

class RangeSubsetGenerator
{
    /**
     * Derive an subset of a range array based upon characteristics of a candidate permit
     *
     *
     * @return array
     */
    public function generate(IrhpCandidatePermit $candidatePermit, array $ranges)
    {
        $emissionsCategoryId = $candidatePermit->getAssignedEmissionsCategory()->getId();
        $rangesSubset = [];

        foreach ($ranges as $range) {
            if ($range['emissionsCategory'] == $emissionsCategoryId) {
                $rangesSubset[] = $range;
            }
        }

        return $rangesSubset;
    }
}
