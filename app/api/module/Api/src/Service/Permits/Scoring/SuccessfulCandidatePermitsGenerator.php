<?php

namespace Dvsa\Olcs\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Entity\System\RefData;
use RuntimeException;

class SuccessfulCandidatePermitsGenerator
{
    public const ID_KEY = 'id';
    public const EMISSIONS_CATEGORY_KEY = 'emissions_category';

    /**
     * Create service instance
     *
     *
     * @return SuccessfulCandidatePermitsGenerator
     */
    public function __construct(private readonly EmissionsCategoryAvailabilityCounter $emissionsCategoryAvailabilityCounter)
    {
    }

    /**
     * Get an array containing successful candidate permit ids and the emissions categories assigned to them
     *
     * @param int $stockId
     * @param int $quotaRemaining
     * @return array
     */
    public function generate(array $candidatePermits, $stockId, $quotaRemaining)
    {
        $euro5Remaining = $this->emissionsCategoryAvailabilityCounter->getCount(
            $stockId,
            RefData::EMISSIONS_CATEGORY_EURO5_REF
        );

        $euro6Remaining = $this->emissionsCategoryAvailabilityCounter->getCount(
            $stockId,
            Refdata::EMISSIONS_CATEGORY_EURO6_REF
        );

        $successfulCandidatePermits = [];

        foreach ($candidatePermits as $candidatePermit) {
            if (!$quotaRemaining) {
                break;
            }

            $candidatePermitId = $candidatePermit[self::ID_KEY];
            $requestedEmissionsCategory = $candidatePermit[self::EMISSIONS_CATEGORY_KEY];

            if ($requestedEmissionsCategory == RefData::EMISSIONS_CATEGORY_EURO6_REF) {
                if ($euro6Remaining > 0) {
                    $assignedEmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO6_REF;
                    $euro6Remaining--;
                } else {
                    if ($euro5Remaining < 1) {
                        throw new RuntimeException(
                            'Assertion failed - euro6 requested but no euro5 or euro6 permits remaining'
                        );
                    }

                    $assignedEmissionsCategory = RefData::EMISSIONS_CATEGORY_EURO5_REF;
                    $euro5Remaining--;
                }
                $quotaRemaining--;

                $successfulCandidatePermits[] = [
                    self::ID_KEY => $candidatePermitId,
                    self::EMISSIONS_CATEGORY_KEY => $assignedEmissionsCategory
                ];
            } else {
                if ($euro5Remaining > 0) {
                    $successfulCandidatePermits[] = [
                        self::ID_KEY => $candidatePermitId,
                        self::EMISSIONS_CATEGORY_KEY => RefData::EMISSIONS_CATEGORY_EURO5_REF
                    ];
                    $euro5Remaining--;
                    $quotaRemaining--;
                }
            }
        }

        return $successfulCandidatePermits;
    }
}
