<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\DeletableInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * IrhpCandidatePermit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_candidate_permit",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_candidate_permits_irhp_permit_applications1_idx",
     *     columns={"irhp_permit_application_id"}),
 *        @ORM\Index(name="fk_irhp_candidate_permit_irhp_permit_range",
     *     columns={"irhp_permit_range_id"}),
 *        @ORM\Index(name="fk_irhp_candidate_permit_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_candidate_permit_last_modified_by_user_id",
     *     columns={"last_modified_by"})
 *    }
 * )
 */
class IrhpCandidatePermit extends AbstractIrhpCandidatePermit implements DeletableInterface
{
    /**
     * Create IRHP Candidate Permit entity
     *
     *
     * @return self
     */
    public static function createNew(
        IrhpPermitApplication $irhpPermitApplication,
        RefData $requestedEmissionsCategory,
        float $intensityOfUse = null,
        float $applicationScore = null
    ) {
        $irhpCandidatePermit = new self();
        $irhpCandidatePermit->irhpPermitApplication = $irhpPermitApplication;
        $irhpCandidatePermit->requestedEmissionsCategory = $requestedEmissionsCategory;
        $irhpCandidatePermit->intensityOfUse = $intensityOfUse;
        $irhpCandidatePermit->applicationScore = $applicationScore;
        $irhpCandidatePermit->successful = 0;
        $irhpCandidatePermit->wanted = 1;

        return $irhpCandidatePermit;
    }

    /**
     * Create an instance for use in an APGG context
     *
     *
     * @return self
     */
    public static function createForApgg(IrhpPermitApplication $irhpPermitApplication, IrhpPermitRange $irhpPermitRange)
    {
        $irhpCandidatePermit = new self();
        $irhpCandidatePermit->irhpPermitApplication = $irhpPermitApplication;
        $irhpCandidatePermit->irhpPermitRange = $irhpPermitRange;
        $irhpCandidatePermit->assignedEmissionsCategory = $irhpPermitRange->getEmissionsCategory();
        $irhpCandidatePermit->successful = 1;
        $irhpCandidatePermit->wanted = 1;

        return $irhpCandidatePermit;
    }

    /**
     * Prepares this candidate permit for use in a scoring run
     */
    public function prepareForScoring()
    {
        $this->successful = 0;
        $this->assignedEmissionsCategory = null;
        $this->irhpPermitRange = null;
    }

    /**
     * Indicates if this candidate permit already has a randomized score set
     *
     * @return bool
     */
    public function hasRandomizedScore()
    {
        return !is_null($this->randomizedScore);
    }

    /**
     * Calculates the randomised score and random factor for this candidate permit
     *
     * @param array $deviationData a pre-formatted array of data for use in calculations
     * @param int $licNo the licence number corresponding to the candidate permit
     */
    public function applyRandomizedScore(array $deviationData, $licNo)
    {
        $standardDeviation = 0;
        $licenceData = $deviationData['licenceData'][$licNo];
        foreach ($licenceData as $applicationPermitsRequired) {
            $standardDeviation += $applicationPermitsRequired;
        }

        // https://stackoverflow.com/a/15952104
        // https://en.wikipedia.org/wiki/Box%E2%80%93Muller_transform
        $x = mt_rand() / mt_getrandmax();
        $y = mt_rand() / mt_getrandmax();

        $randomFactor = sqrt(-2 * log($x)) * cos(2 * pi() * $y) * $standardDeviation + $deviationData['meanDeviation'];

        $this->randomizedScore = $randomFactor * $this->applicationScore;
        $this->randomFactor = $randomFactor;
    }

    /**
     * Sets the range for this candidate permit
     *
     * @param IrhpPermitRange $range the range to be applied
     *
     * @throws ForbiddenException
     */
    public function applyRange(IrhpPermitRange $range)
    {
        if ($this->assignedEmissionsCategory !== $range->getEmissionsCategory()) {
            throw new ForbiddenException(
                'A candidate permit can only be assigned to a range with a matching emissions category'
            );
        }

        $this->irhpPermitRange = $range;
    }

    /**
     * Marks this candidate permit as successful and sets the assigned emissions category
     *
     *
     * @throws ForbiddenException
     */
    public function markAsSuccessful(RefData $assignedEmissionsCategory)
    {
        if ($this->successful == 1) {
            throw new ForbiddenException('This candidate permit has already been marked as successful');
        }

        $this->successful = 1;
        $this->assignedEmissionsCategory = $assignedEmissionsCategory;
    }

    /**
     * Updates permit range against candidate permit
     *
     *
     * @throws ForbiddenException
     */
    public function updateIrhpPermitRange(IrhpPermitRange $irhpPermitRange)
    {
        if (!$this->isApplicationUnderConsideration()) {
            throw new ForbiddenException('IRHP Application status does not support changing IRHP Permit Range');
        }
        $this->irhpPermitRange = $irhpPermitRange;

        // update assignedEmissionsCategory based on the new range
        $this->assignedEmissionsCategory = $irhpPermitRange->getEmissionsCategory();
    }

    /**
     * @return bool
     */
    public function canDelete()
    {
        return $this->isApplicationUnderConsideration();
    }

    /**
     * @return bool
     */
    public function isApplicationUnderConsideration()
    {
        return $this->getIrhpPermitApplication()
            ->getIrhpApplication()
            ->isUnderConsideration();
    }

    /**
     * Revive this candidate permit from an unsuccessful state
     */
    public function reviveFromUnsuccessful()
    {
        $this->randomizedScore = null;
        $this->randomFactor = null;
        $this->prepareForScoring();
    }

    /**
     * Set whether this candidate permit is wanted by the applicant
     *
     * @param bool $wanted
     */
    public function updateWanted($wanted)
    {
        $this->wanted = $wanted;
    }
}
