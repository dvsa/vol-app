<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\DeletableInterface;
use Dvsa\Olcs\Api\Entity\Generic\ApplicationPathGroup;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteria;
use Dvsa\Olcs\Api\Service\Permits\Allocate\RangeMatchingCriteriaInterface;
use RuntimeException;

/**
 * IrhpPermitStock Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit_stock",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permit_stock_irhp_permit_types1_idx",
 *     columns={"irhp_permit_type_id"})
 *    }
 * )
 */
class IrhpPermitStock extends AbstractIrhpPermitStock implements DeletableInterface
{
    public const STATUS_SCORING_NEVER_RUN = 'stock_scoring_never_run';
    public const STATUS_SCORING_PENDING = 'stock_scoring_pending';
    public const STATUS_SCORING_IN_PROGRESS = 'stock_scoring_in_progress';
    public const STATUS_SCORING_SUCCESSFUL = 'stock_scoring_successful';
    public const STATUS_SCORING_PREREQUISITE_FAIL = 'stock_scoring_prereq_fail';
    public const STATUS_SCORING_UNEXPECTED_FAIL = 'stock_scoring_unexpected_fail';
    public const STATUS_ACCEPT_PENDING = 'stock_accept_pending';
    public const STATUS_ACCEPT_IN_PROGRESS = 'stock_accept_in_progress';
    public const STATUS_ACCEPT_SUCCESSFUL = 'stock_accept_successful';
    public const STATUS_ACCEPT_PREREQUISITE_FAIL = 'stock_accept_prereq_fail';
    public const STATUS_ACCEPT_UNEXPECTED_FAIL = 'stock_accept_unexpected_fail';

    public const ALLOCATION_MODE_STANDARD = 'allocation_mode_standard';
    public const ALLOCATION_MODE_EMISSIONS_CATEGORIES = 'allocation_mode_emissions_categories';
    public const ALLOCATION_MODE_STANDARD_WITH_EXPIRY = 'allocation_mode_standard_expiry';
    public const ALLOCATION_MODE_CANDIDATE_PERMITS = 'allocation_mode_candidate_permits';
    public const ALLOCATION_MODE_BILATERAL = 'allocation_mode_bilateral';
    public const ALLOCATION_MODE_NONE = 'allocation_mode_none';

    public const CANDIDATE_MODE_APSG = 'candidate_mode_apsg';
    public const CANDIDATE_MODE_APGG = 'candidate_mode_apgg';
    public const CANDIDATE_MODE_NONE = 'candidate_mode_none';

    public const APGG_SHORT_TERM_NO_CANDIDATES_YEAR = 2019;

    /**
     * @param int $quota
     * @param bool $hiddenSs
     *
     * @return IrhpPermitStock
     * @throws ValidationException
     */
    public static function create(
        IrhpPermitType $type,
        ?Country $country,
        ?RefData $permitCategory,
        $quota,
        RefData $status,
        ?ApplicationPathGroup $applicationPathGroup,
        ?RefData $businessProcess,
        ?string $periodNameKey = null,
        mixed $validFrom = null,
        mixed $validTo = null,
        $hiddenSs = false
    ) {
        static::validateCountry($type, $country, $permitCategory);

        $instance = new self();

        $instance->irhpPermitType = $type;
        $instance->country = $country;
        $instance->permitCategory = $permitCategory;
        $instance->validFrom = static::processDate($validFrom, 'Y-m-d');
        $instance->validTo = static::processDate($validTo, 'Y-m-d');
        $instance->initialStock = intval($quota) > 0 ? $quota : 0;
        $instance->status = $status;
        $instance->applicationPathGroup = $applicationPathGroup;
        $instance->businessProcess = $businessProcess;
        $instance->periodNameKey = $periodNameKey;
        $instance->hiddenSs = $hiddenSs;

        return $instance;
    }

    /**
     * @param Country $country
     * @param int $quota
     * @param string|null $periodNameKey
     * @param bool $hiddenSs
     *
     * @return $this
     * @throws ValidationException
     */
    public function update(
        IrhpPermitType $type,
        ?Country $country,
        $permitCategory,
        $quota,
        $periodNameKey = null,
        mixed $validFrom = null,
        mixed $validTo = null,
        $hiddenSs = false
    ) {
        static::validateCountry($type, $country, $permitCategory);

        $this->irhpPermitType = $type;
        $this->country = $country;
        $this->permitCategory = $permitCategory;
        $this->validFrom = static::processDate($validFrom, 'Y-m-d');
        $this->validTo = static::processDate($validTo, 'Y-m-d');
        $this->initialStock = intval($quota) > 0 ? $quota : 0;
        $this->periodNameKey = $periodNameKey;
        $this->hiddenSs = $hiddenSs;

        return $this;
    }

    /**
     * Enforces business logic that a Bilateral Permit MUST have a country specified
     *
     * @param Country $country
     *
     * @throws ValidationException
     */
    private static function validateCountry(IrhpPermitType $type, ?Country $country, ?RefData $permitCategory)
    {
        if ($type->getId() === IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL) {
            if (is_null($country)) {
                throw new ValidationException(['You must select a country for this permit type']);
            }

            if ($country->getId() == Country::ID_MOROCCO) {
                if (is_null($permitCategory)) {
                    throw new ValidationException(['Permit category must be specified for Bilateral Morocco stocks']);
                }
            } elseif (!is_null($permitCategory)) {
                throw new ValidationException(['Permit category is only applicable for Bilateral Morocco stocks']);
            }
        }
    }

    /**
     * Checks if there are ranges existing on the Permit Stock
     *
     * @return boolean
     */
    private function canDeletePermitRanges()
    {
        return count($this->irhpPermitRanges) === 0;
    }

    /**
     * Checks if there are windows existing on the Permit Stock.
     *
     * @return boolean
     */
    private function canDeletePermitWindows()
    {
        return count($this->irhpPermitWindows) === 0;
    }

    /**
     * Checks whether the Permit Stock can be deleted.
     *
     * @return boolean
     */
    public function canDelete()
    {
        return
            $this->canDeletePermitRanges() &&
            $this->canDeletePermitWindows();
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'canDelete' => $this->canDelete(),
            'hasEuro5Range' => $this->hasEuro5Range(),
            'hasEuro6Range' => $this->hasEuro6Range(),
            'validityYear' => $this->getValidityYear(),
        ];
    }

    /**
     * Returns the description of the status of this stock
     *
     * @return string
     */
    public function getStatusDescription()
    {
        return $this->status->getDescription();
    }

    /**
     * Indicates if the status of the stock allows the run scoring process to be queued for execution (subject to
     * further checks outside the boundary of this entity)
     *
     * @return bool
     */
    public function statusAllowsQueueRunScoring()
    {
        return in_array(
            $this->status->getId(),
            [
                self::STATUS_SCORING_NEVER_RUN,
                self::STATUS_SCORING_SUCCESSFUL,
                self::STATUS_SCORING_PREREQUISITE_FAIL,
                self::STATUS_SCORING_UNEXPECTED_FAIL,
                self::STATUS_ACCEPT_PREREQUISITE_FAIL,
                self::STATUS_ACCEPT_SUCCESSFUL,
            ]
        );
    }

    /**
     * Indicates if the status of the stock allows the run scoring process to be executed (subject to further checks
     * outside the boundary of this entity)
     *
     * @return bool
     */
    public function statusAllowsRunScoring()
    {
        return $this->isScoringPending();
    }

    /**
     * Indicates if the status of the stock allows the accept scoring process to be queued for execution or the post
     * scoring report to be generated (subject to further checks outside the boundary of this entity)
     *
     * @return bool
     */
    public function statusAllowsQueueAcceptScoringAndPostScoringReport()
    {
        return in_array(
            $this->status->getId(),
            [
                self::STATUS_SCORING_SUCCESSFUL,
                self::STATUS_ACCEPT_PREREQUISITE_FAIL,
                self::STATUS_ACCEPT_UNEXPECTED_FAIL,
                self::STATUS_ACCEPT_SUCCESSFUL,
            ]
        );
    }

    /**
     * Indicates if the status of the stock allows the accept scoring process to be executed (subject to further checks
     * outside the boundary of this entity)
     *
     * @return bool
     */
    public function statusAllowsAcceptScoring()
    {
        return $this->isAcceptPending();
    }

    /**
     * Update the status of the stock to indicate that the scoring process has been queued
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToScoringPending(RefData $scoringPendingStatus)
    {
        if (!$this->statusAllowsQueueRunScoring()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to scoring pending (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $scoringPendingStatus;
    }

    /**
     * Update the status of the stock to indicate that the scoring process could not start due to one or more failed
     * failed prerequisites
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToScoringPrerequisiteFail(RefData $scoringPrerequisiteFailStatus)
    {
        if (!$this->isScoringPending()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to scoring prerequisite fail (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $scoringPrerequisiteFailStatus;
    }

    /**
     * Attempt to update the status of the stock to indicate that the scoring process is in progress
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToScoringInProgress(RefData $scoringInProgressStatus)
    {
        if (!$this->isScoringPending()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to scoring in progress (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $scoringInProgressStatus;
    }

    /**
     * Attempt to update the status of the stock to indicate that the scoring process has been successfully completed
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToScoringSuccessful(RefData $scoringSuccessfulStatus)
    {
        if (!$this->isScoringInProgress()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to scoring successful (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $scoringSuccessfulStatus;
    }

    /**
     * Attempt to update the status of the stock to indicate that an unexpected failure happened whilst stock scoring
     * was in progress
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToScoringUnexpectedFail(RefData $scoringUnexpectedFailStatus)
    {
        if (!$this->isScoringInProgress()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to scoring unexpected fail (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $scoringUnexpectedFailStatus;
    }

   /**
     * Update the status of the stock to indicate that the accept process has been queued
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToAcceptPending(RefData $acceptPendingStatus)
    {
        if (!$this->statusAllowsQueueAcceptScoringAndPostScoringReport()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to accept pending (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $acceptPendingStatus;
    }

    /**
     * Update the status of the stock to indicate that the accept process could not start due to one or more failed
     * failed prerequisites
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToAcceptPrerequisiteFail(RefData $acceptPrerequisiteFailStatus)
    {
        if (!$this->isAcceptPending()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to accept prerequisite fail (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $acceptPrerequisiteFailStatus;
    }

    /**
     * Attempt to update the status of the stock to indicate that the accept process is in progress
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToAcceptInProgress(RefData $acceptInProgressStatus)
    {
        if (!$this->isAcceptPending()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to accept in progress (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $acceptInProgressStatus;
    }

    /**
     * Attempt to update the status of the stock to indicate that the accept process has been successfully completed
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToAcceptSuccessful(RefData $acceptSuccessfulStatus)
    {
        if (!$this->isAcceptInProgress()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to accept successful (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $acceptSuccessfulStatus;
    }

    /**
     * Attempt to update the status of the stock to indicate that an unexpected failure happened whilst stock accept
     * was in progress
     *
     *
     * @throws ForbiddenException
     */
    public function proceedToAcceptUnexpectedFail(RefData $acceptUnexpectedFailStatus)
    {
        if (!$this->isAcceptInProgress()) {
            throw new ForbiddenException(
                sprintf(
                    'This stock is not in the correct status to proceed to accept unexpected fail (%s)',
                    $this->getStatusDescription()
                )
            );
        }

        $this->status = $acceptUnexpectedFailStatus;
    }

    /**
     * Indicates if the running of the scoring process is currently queued on this stock
     *
     * @return bool
     */
    private function isScoringPending()
    {
        return $this->status->getId() == self::STATUS_SCORING_PENDING;
    }

    /**
     * Indicates if the running of the scoring process is currently in progress on this stock
     *
     * @return bool
     */
    private function isScoringInProgress()
    {
        return $this->status->getId() == self::STATUS_SCORING_IN_PROGRESS;
    }

    /**
     * Indicates if the running of the acceptance process is currently queued on this stock
     *
     * @return bool
     */
    private function isAcceptPending()
    {
        return $this->status->getId() == self::STATUS_ACCEPT_PENDING;
    }

    /**
     * Indicates if the running of the acceptance process is currently in progress on this stock
     *
     * @return bool
     */
    private function isAcceptInProgress()
    {
        return $this->status->getId() == self::STATUS_ACCEPT_IN_PROGRESS;
    }

    /**
     * Get non-reserved, non-replacement ranges relating to this stock ordered by from no
     *
     * @param RangeMatchingCriteriaInterface $criteria (optional)
     *
     * @return ArrayCollection
     */
    public function getNonReservedNonReplacementRangesOrderedByFromNo(?RangeMatchingCriteriaInterface $rangeMatchingCriteria = null)
    {
        $criteria = Criteria::create();

        $criteria->where($criteria->expr()->eq('ssReserve', false))
            ->andWhere($criteria->expr()->eq('lostReplacement', false))
            ->orderBy(['fromNo' => Criteria::ASC]);

        $ranges = $this->getIrhpPermitRanges()->matching($criteria);

        if (is_null($rangeMatchingCriteria)) {
            return $ranges;
        }

        $filteredRanges = new ArrayCollection();
        foreach ($ranges as $range) {
            if ($rangeMatchingCriteria->matches($range)) {
                $filteredRanges->add($range);
            }
        }

        return $filteredRanges;
    }

    /**
     * Whether the stock has an open permit application window
     *
     * @return IrhpPermitWindow|null
     */
    public function getOpenWindow()
    {
        /** @var IrhpPermitWindow $window */
        foreach ($this->irhpPermitWindows as $window) {
            if ($window->isActive()) {
                return $window;
            }
        }

        return null;
    }

    /**
     * Whether the stock has an open permit application window
     *
     * @return bool
     */
    public function hasOpenWindow(): bool
    {
        return !is_null(
            $this->getOpenWindow()
        );
    }

    /**
     * Get the validity year of this stock
     *
     * @return int
     */
    public function getValidityYear()
    {
        return is_null($this->getValidTo(true)) ? null : $this->getValidTo(true)->format('Y');
    }

    /**
     * Does stock have a Euro5 Range?
     *
     * @return bool
     */
    public function hasEuro5Range()
    {
        return($this->hasRangeWithEmissionsCat(RefData::EMISSIONS_CATEGORY_EURO5_REF));
    }

    /**
     * Does stock have a Euro6 Range?
     *
     * @return bool
     */
    public function hasEuro6Range()
    {
        return($this->hasRangeWithEmissionsCat(RefData::EMISSIONS_CATEGORY_EURO6_REF));
    }

    /**
     * Does stock have a range with given emissions category ref data id?
     *
     * @return bool
     */
    protected function hasRangeWithEmissionsCat(string $emissionsRef)
    {
        $ranges = $this->getIrhpPermitRanges();
        foreach ($ranges as $range) {
            if ($range->getEmissionsCategory()->getId() == $emissionsRef) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does stock have a cabotage range?
     *
     * @return bool
     */
    public function hasCabotageRange()
    {
        $ranges = $this->getIrhpPermitRanges();

        foreach ($ranges as $range) {
            if ($range->isCabotage()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Does stock have a standard range?
     *
     * @return bool
     */
    public function hasStandardRange()
    {
        $ranges = $this->getIrhpPermitRanges();

        foreach ($ranges as $range) {
            if ($range->isStandard()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the first available unreserved range with no countries matching the specified emissions category
     *
     *
     * @return IrhpPermitRange
     * @throws RuntimeException
     */
    public function getFirstAvailableRangePreferWithNoCountries(EmissionsStandardCriteria $criteria)
    {
        $ranges = $this->getNonReservedNonReplacementRangesOrderedByFromNo($criteria);

        if ($ranges->isEmpty()) {
            throw new RuntimeException('Unable to find available range');
        }

        /** @var IrhpPermitRange $range */
        foreach ($ranges as $range) {
            if (!$range->hasCountries()) {
                return $range;
            }
        }

        return $ranges->first();
    }

    /**
     * Get the permit allocation mode used by this stock
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function getAllocationMode()
    {
        $irhpPermitTypeId = $this->irhpPermitType->getId();
        $businessProcessId = $this->businessProcess->getId();

        $isEcmtShortTerm = ($irhpPermitTypeId == IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM);
        $isApgg = ($businessProcessId == RefData::BUSINESS_PROCESS_APGG);

        if ($isEcmtShortTerm && $isApgg && $this->getValidityYear() == self::APGG_SHORT_TERM_NO_CANDIDATES_YEAR) {
            return self::ALLOCATION_MODE_EMISSIONS_CATEGORIES;
        }

        $mappings = [
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                'business_process' => RefData::BUSINESS_PROCESS_APSG,
                'allocation_mode' => self::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                'business_process' => RefData::BUSINESS_PROCESS_APG,
                'allocation_mode' => self::ALLOCATION_MODE_BILATERAL,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL,
                'business_process' => RefData::BUSINESS_PROCESS_APG,
                'allocation_mode' => self::ALLOCATION_MODE_STANDARD,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'business_process' => RefData::BUSINESS_PROCESS_APGG,
                'allocation_mode' => self::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
                'business_process' => RefData::BUSINESS_PROCESS_APSG,
                'allocation_mode' => self::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
                'business_process' => RefData::BUSINESS_PROCESS_APG,
                'allocation_mode' => self::ALLOCATION_MODE_STANDARD_WITH_EXPIRY,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                'business_process' => RefData::BUSINESS_PROCESS_APSG,
                'allocation_mode' => self::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_VEHICLE,
                'business_process' => RefData::BUSINESS_PROCESS_AG,
                'allocation_mode' => self::ALLOCATION_MODE_NONE,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_CERT_ROADWORTHINESS_TRAILER,
                'business_process' => RefData::BUSINESS_PROCESS_AG,
                'allocation_mode' => self::ALLOCATION_MODE_NONE,
            ],
            [
                'type' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                'business_process' => RefData::BUSINESS_PROCESS_APGG,
                'allocation_mode' => self::ALLOCATION_MODE_CANDIDATE_PERMITS,
            ],
        ];

        foreach ($mappings as $mapping) {
            if ($mapping['type'] == $irhpPermitTypeId && $mapping['business_process'] == $businessProcessId) {
                return $mapping['allocation_mode'];
            }
        }

        throw new RuntimeException(
            sprintf(
                'No allocation mode set for permit type %s and business process %s',
                $irhpPermitTypeId,
                $businessProcessId
            )
        );
    }

    /**
     * Get the method to be used for candidate permit creation upon application submission
     *
     * @return string
     */
    public function getCandidatePermitCreationMode()
    {
        $businessProcessId = $this->businessProcess->getId();

        switch ($businessProcessId) {
            case RefData::BUSINESS_PROCESS_APGG:
                $isShortTerm = $this->irhpPermitType->isEcmtShortTerm();
                if ($isShortTerm && $this->getValidityYear() == self::APGG_SHORT_TERM_NO_CANDIDATES_YEAR) {
                    return self::CANDIDATE_MODE_NONE;
                }

                return self::CANDIDATE_MODE_APGG;
            case RefData::BUSINESS_PROCESS_APSG:
                return self::CANDIDATE_MODE_APSG;
        }

        return self::CANDIDATE_MODE_NONE;
    }

    /**
     * Get list of permit usage for the stock
     *
     * @return array
     */
    public function getPermitUsageList()
    {
        $criteria = Criteria::create();

        $criteria->where($criteria->expr()->eq('ssReserve', false))
            ->andWhere($criteria->expr()->eq('lostReplacement', false))
            ->andWhere($criteria->expr()->neq('journey', null));

        $ranges = $this->getIrhpPermitRanges()->matching($criteria);

        $result = [];

        foreach ($ranges as $range) {
            $journey = $range->getJourney();

            $result[$journey->getDisplayOrder()] = $journey;
        }

        ksort($result);

        return $result;
    }

    /**
     * Whether the country associated with this stock is Morocco
     *
     * @return bool
     */
    public function isMorocco()
    {
        return $this->country->isMorocco();
    }

    /**
     * Return the translation key to be used to represent the period selection page on bilateral
     *
     * @param bool $isMultiStock
     *
     * @return string
     */
    public function getBilateralAnswerSummaryLabelKey($isMultiStock = false)
    {
        if ($this->isMorocco()) {
            return 'permits.page.bilateral.permit-needed';
        }

        return $isMultiStock ? 'permits.page.bilateral.which-period-required.multi-stock' : 'permits.page.bilateral.which-period-required';
    }

    /**
     * Get list of excluded restricted country ids
     *
     * @return array
     */
    public function getExcludedRestrictedCountryIds()
    {
        $excludedRestrictedCountryIds = [];

        if ($this->irhpPermitType->isEcmtAnnual() && $this->hasEuro5Range() && !$this->hasEuro6Range()) {
            $excludedRestrictedCountryIds[] = Country::ID_AUSTRIA;
        }

        return $excludedRestrictedCountryIds;
    }

    /**
     * Whether this is a Certificate of Roadworthiness stock
     *
     * @return bool
     */
    public function isCertificateOfRoadworthiness()
    {
        return $this->irhpPermitType->isCertificateOfRoadworthiness();
    }
}
