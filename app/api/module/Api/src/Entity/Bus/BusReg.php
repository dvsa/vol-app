<?php

namespace Dvsa\Olcs\Api\Entity\Bus;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod as BusNoticePeriodEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusShortNotice as BusShortNoticeEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusRegOtherService as BusRegOtherServiceEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Task\Task as TaskEntity;
use Dvsa\Olcs\Api\Service\Document\ContextProviderInterface;
use Doctrine\Common\Collections\Criteria;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * BusReg Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="bus_reg",
 *    indexes={
 *        @ORM\Index(name="ix_bus_reg_licence_id", columns={"licence_id"}),
 *        @ORM\Index(name="ix_bus_reg_bus_notice_period_id", columns={"bus_notice_period_id"}),
 *        @ORM\Index(name="ix_bus_reg_subsidised", columns={"subsidised"}),
 *        @ORM\Index(name="ix_bus_reg_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_bus_reg_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_bus_reg_withdrawn_reason", columns={"withdrawn_reason"}),
 *        @ORM\Index(name="ix_bus_reg_status", columns={"status"}),
 *        @ORM\Index(name="ix_bus_reg_revert_status", columns={"revert_status"}),
 *        @ORM\Index(name="ix_bus_reg_reg_no", columns={"reg_no"}),
 *        @ORM\Index(name="fk_bus_reg_parent_id_bus_reg_id", columns={"parent_id"}),
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_bus_reg_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class BusReg extends AbstractBusReg implements ContextProviderInterface, OrganisationProviderInterface
{
    public const STATUS_NEW = 'breg_s_new';
    public const STATUS_VAR = 'breg_s_var';
    public const STATUS_CANCEL = 'breg_s_cancellation';
    public const STATUS_ADMIN = 'breg_s_admin';
    public const STATUS_REGISTERED = 'breg_s_registered';
    public const STATUS_REFUSED = 'breg_s_refused';
    public const STATUS_WITHDRAWN = 'breg_s_withdrawn';
    public const STATUS_CNS = 'breg_s_cns';
    public const STATUS_CANCELLED = 'breg_s_cancelled';
    public const STATUS_EXPIRED = 'breg_s_expired';

    public const SUBSIDY_NO = 'bs_no';

    public const TXC_APP_NEW = 'new';
    public const TXC_APP_CANCEL = 'cancel';
    public const TXC_APP_CHARGEABLE = 'chargeableChange';
    public const TXC_APP_NON_CHARGEABLE = 'nonChargeableChange';

    public const FORBIDDEN_NO_PERMISSION_ERROR = 'No permission to edit this record';

    /**
     * Statuses to be included in a registration history list
     *
     * @var array
     */
    public static $registrationHistoryStatuses = [
        self::STATUS_NEW,
        self::STATUS_REGISTERED,
        self::STATUS_VAR,
        self::STATUS_CANCEL,
        self::STATUS_CANCELLED
    ];

    /**
     * @var array
     */
    private static $grantStatusMap = [
        self::STATUS_NEW => self::STATUS_REGISTERED,
        self::STATUS_VAR => self::STATUS_REGISTERED,
        self::STATUS_CANCEL => self::STATUS_CANCELLED,
    ];

    /**
     * @var array
     */
    private static $defaultAll = [
        // Reason for action text fields should all be empty
        'reasonSnRefused' => '',
        'reasonCancelled' => '',
        'reasonRefused' => '',
        // Withdrawn reason can be null; its here to override any value set in a variation/cancellation
        'withdrawnReason' => null,
        // At time of creation, we don't know if its short notice or not. Default to no.
        'isShortNotice' => 'N',
        // This is a new application/variation so hasn't been refused by short notice (yet)
        'shortNoticeRefused' => 'N',
        // Checks before granting should all default to no
        'copiedToLaPte' => 'N',
        'laShortNote' => 'N',
        'applicationSigned' => 'N',
        'opNotifiedLaPte' => 'N',
        // Trc conditions should also default to no/empty
        'trcConditionChecked' => 'N',
        'trcNotes' => '',
        // Timetable conditions should default to no
        'timetableAcceptable' => 'N',
        'mapSupplied' => 'N',
        // (Re)set dates to null
        'receivedDate' => null,
        'effectiveDate' => null,
        'endDate' => null,
        'applicationCompleteDate' => null,
        // These will be set to yes explicitly by the TXC processor, default it to no for the internal app
        'isTxcApp' => 'N',
        'ebsrRefresh' => 'N'
    ];

    /**
     * Create new BusReg
     *
     * @param LicenceEntity         $licence         Licence
     * @param RefData               $status          Status
     * @param RefData               $revertStatus    Revert status
     * @param RefData               $subsidised      Subsidised
     * @param BusNoticePeriodEntity $busNoticePeriod Bus notice period
     *
     * @return BusReg
     */
    public static function createNew(
        LicenceEntity $licence,
        RefData $status,
        RefData $revertStatus,
        RefData $subsidised,
        BusNoticePeriodEntity $busNoticePeriod
    ) {
        // get default data
        $data = array_merge(
            self::$defaultAll,
            [
                'variationNo' => 0,
                // Should this be moved to all? and the details field wiped?
                'needNewStop' => 'N',
                'hasManoeuvre' => 'N',
                'hasNotFixedStop' => 'N',
                // Reg number is generated based upon the licence and route number. empty by default.
                'regNo' => '',
                'routeNo' => 0,
                // Some discussion over what value of this should be John Spellman has now confirmed it
                'useAllStops' => 'N',
                'isQualityContract' => 'N',
                'isQualityPartnership' => 'N',
                'qualityPartnershipFacilitiesUsed' => 'N'
            ]
        );

        // create bus reg with defaults
        $busReg = new self();
        $busReg->fromData($data);

        // set reference data
        $busReg->setLicence($licence);
        $busReg->setStatus($status);
        $busReg->setRevertStatus($revertStatus);
        $busReg->setSubsidised($subsidised);
        $busReg->setBusNoticePeriod($busNoticePeriod);

        // set default short notice
        $busShortNotice = new BusShortNoticeEntity();
        $busShortNotice->setBusReg($busReg);
        $busReg->setShortNotice($busShortNotice);

        // get the most recent Route No for the licence and increment it
        $newRouteNo = (int)$licence->getLatestBusRouteNo() + 1;
        $busReg->setRouteNo($newRouteNo);

        // set Reg No
        $regNo = $licence->getLicNo() . '/' . $newRouteNo;
        $busReg->setRegNo($regNo);

        return $busReg;
    }

    /**
     * Returns whether the bus reg is an application for cancellation
     *
     * @return bool
     */
    public function isCancellation()
    {
        return $this->status->getId() === self::STATUS_CANCEL;
    }

    /**
     * Returns whether the bus reg is cancelled
     *
     * @return bool
     */
    public function isCancelled()
    {
        return $this->status->getId() === self::STATUS_CANCELLED;
    }

    /**
     * Returns whether the bus reg is a registered bus route
     *
     * @return bool
     */
    public function isRegistered()
    {
        return $this->status->getId() === self::STATUS_REGISTERED;
    }

    /**
     * Returns whether a variation can be created
     *
     * @return bool
     */
    public function canCreateVariation()
    {
        return $this->isRegistered()
            && $this->isLatestVariation();
    }

    /**
     * Create BusReg variation
     *
     * @param RefData $status       Status
     * @param RefData $revertStatus Revert status
     *
     * @throws ForbiddenException
     * @return BusReg
     */
    public function createVariation(
        RefData $status,
        RefData $revertStatus
    ) {
        if (!$this->canCreateVariation()) {
            throw new ForbiddenException('Can only create a variation/cancellation against a registered bus route');
        }

        // create bus reg based on the previous record
        $busReg = clone $this;

        // override columns which need different defaults for a variation
        $data = array_merge(
            self::$defaultAll,
            [
                // unset database metadata
                'id' => null,
                'version' => null,
                'createdBy' => null,
                'lastModifiedBy' => null,
                'createdOn' => null,
                'lastModifiedOn' => null,
                'olbsKey' => null,
                // new variation reasons will be required for a new variation
                'variationReasons' => null,
            ]
        );
        $busReg->fromData($data);

        // set parent
        $busReg->setParent($this);

        // set reference data
        $busReg->setStatus($status);
        $busReg->setStatusChangeDate(new \DateTime());
        $busReg->setRevertStatus($revertStatus);

        //increment variation number
        $latestVariation = $busReg->getLicence()->getLatestBusVariation($busReg->getRegNo(), []);
        $newVariationNo = (int)$latestVariation->getVariationNo() + 1;
        $busReg->setVariationNo($newVariationNo);

        // set default short notice
        $busShortNotice = new BusShortNoticeEntity();
        $busShortNotice->setBusReg($busReg);
        $busReg->setShortNotice($busShortNotice);

        // make a copy of otherServices
        $otherServices = new ArrayCollection();

        foreach ($this->getOtherServices() as $otherService) {
            $newOtherService = clone $otherService;

            // unset database metadata
            $newOtherService->setId(null);
            $newOtherService->setVersion(null);
            $newOtherService->setCreatedBy(null);
            $newOtherService->setLastModifiedBy(null);
            $newOtherService->setCreatedOn(null);
            $newOtherService->setLastModifiedOn(null);
            $newOtherService->setOlbsKey(null);
            $newOtherService->setBusReg($busReg);

            $otherServices->add($newOtherService);
        }
        $busReg->setOtherServices($otherServices);

        return $busReg;
    }

    /**
     * Populate properties from data
     *
     * @param array $data Data
     *
     * @return void
     */
    public function fromData($data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucwords((string) $key);

            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }
    }

    /**
     * A bus reg may only be edited if it's the latest variation
     *
     * @return bool
     * @throws ForbiddenException
     */
    public function canEdit()
    {
        if ($this->isLatestVariation()) {
            return true;
        }

        throw new ForbiddenException(self::FORBIDDEN_NO_PERMISSION_ERROR);
    }

    /**
     * A bus reg may only be deleted if it's the latest variation
     *
     * @return bool
     * @throws ForbiddenException
     */
    public function canDelete()
    {
        if ($this->isLatestVariation()) {
            return true;
        }

        throw new ForbiddenException('Only the latest variation may be deleted');
    }

    /**
     * A decision about a bus reg can be made only if it's the latest variation
     *
     * @return bool
     * @throws ForbiddenException
     */
    public function canMakeDecision()
    {
        if ($this->isLatestVariation()) {
            return true;
        }

        throw new ForbiddenException('Decision can be made on the latest variation only');
    }

    /**
     * Returns is record variation or not
     *
     * @return bool
     */
    public function isVariation()
    {
        return ((int)$this->getVariationNo() > 0);
    }

    /**
     * Returns whether the variation is the latest one
     *
     * @return bool
     */
    public function isLatestVariation()
    {
        $latestBusVariation = $this->getLicence()->getLatestBusVariation($this->getRegNo());

        if (empty($latestBusVariation)) {
            return true;
        }
        return $this->getId() === $latestBusVariation->getId();
    }

    /**
     * Returns whether the record is from EBSR
     *
     * @return bool
     */
    public function isFromEbsr()
    {
        return ($this->isTxcApp === 'Y' ? true : false);
    }

    /**
     * Returns whether the bus reg came from EBSR data refresh
     *
     * @return bool
     */
    public function isEbsrRefresh()
    {
        if ($this->ebsrSubmissions->isEmpty()) {
            return false;
        }

        /** @var EbsrSubmission $ebsrSubmission */
        $ebsrSubmission = $this->ebsrSubmissions->first();

        return $ebsrSubmission->isDataRefresh();
    }

    /**
     * Returns whether the record uses Scottish rules
     *
     * @return bool
     */
    public function isScottishRules()
    {
        return $this->busNoticePeriod->isScottishRules();
    }

    /**
     * Returns whether a bus reg is read only based on status and variation.
     *
     * @note Ebsr is not checked (EBSR pages are often read only on the front end) - to check Ebsr use isFromEbsr()
     *
     * @return bool
     */
    public function isReadOnly()
    {
        return !$this->isLatestVariation() ||
            in_array($this->status->getId(), [self::STATUS_CANCELLED]);
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedValues()
    {
        return [
            'licence' => null,
            'parent' => null,
            'isLatestVariation' => $this->isLatestVariation(),
            'isReadOnly' => $this->isReadOnly(),
            'isScottishRules' => $this->isScottishRules(),
            'isFromEbsr' => $this->isFromEbsr(),
            'shortNotice' => null
        ];
    }

    /**
     * Gets calculated values
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'isLatestVariation' => $this->isLatestVariation(),
            'isReadOnly' => $this->isReadOnly(),
            'isScottishRules' => $this->isScottishRules(),
            'isFromEbsr' => $this->isFromEbsr(),
            'isCancelled' => $this->isCancelled(),
            'isCancellation' => $this->isCancellation(),
            'canWithdraw' => $this->canWithdraw(),
            'canRefuse' => $this->canRefuse(),
            'canRefuseByShortNotice' => $this->canRefuseByShortNotice(),
            'canCreateCancellation' => $this->canCreateCancellation(),
            'canPrint' => $this->canPrint(),
            'canRequestNewRouteMap' => $this->canRequestNewRouteMap(),
            'canRepublish' => $this->canRepublish(),
            'canCancelByAdmin' => $this->canCancelByAdmin(),
            'canResetRegistration' => $this->canResetRegistration(),
            'canCreateVariation' => $this->canCreateVariation(),
        ];
    }

    /**
     * Update stops
     *
     * @param string  $useAllStops        Use all stops
     * @param string  $hasManoeuvre       Has manoeuvre
     * @param string  $manoeuvreDetail    Manoeuvre detail
     * @param string  $needNewStop        Need new stop
     * @param string  $newStopDetail      New stop detail
     * @param string  $hasNotFixedStop    Has not fixed stop
     * @param string  $notFixedStopDetail Not fixed stop detail
     * @param RefData $subsidised         Subsidised
     * @param string  $subsidyDetail      Subsidy detail
     *
     * @return bool
     */
    public function updateStops(
        $useAllStops,
        $hasManoeuvre,
        $manoeuvreDetail,
        $needNewStop,
        $newStopDetail,
        $hasNotFixedStop,
        $notFixedStopDetail,
        $subsidised,
        $subsidyDetail
    ) {
        $this->canEdit();

        $this->setUseAllStops($useAllStops);
        $this->setHasManoeuvre($hasManoeuvre);
        $this->setManoeuvreDetail($manoeuvreDetail);
        $this->setNeedNewStop($needNewStop);
        $this->setNewStopDetail($newStopDetail);
        $this->setHasNotFixedStop($hasNotFixedStop);
        $this->setNotFixedStopDetail($notFixedStopDetail);
        $this->setSubsidised($subsidised);
        $this->setSubsidyDetail($subsidyDetail);

        return true;
    }

    /**
     * Update quality schemes
     *
     * @param string $isQualityPartnership             Is quality partnership
     * @param string $qualityPartnershipDetails        Quality partnership details
     * @param string $qualityPartnershipFacilitiesUsed Quality partnership facilities used
     * @param string $isQualityContract                Is quality contract
     * @param string $qualityContractDetails           Quality contract details
     *
     * @return bool
     */
    public function updateQualitySchemes(
        $isQualityPartnership,
        $qualityPartnershipDetails,
        $qualityPartnershipFacilitiesUsed,
        $isQualityContract,
        $qualityContractDetails
    ) {
        $this->canEdit();

        $this->setIsQualityPartnership($isQualityPartnership);
        $this->setQualityPartnershipDetails($qualityPartnershipDetails);
        $this->setQualityPartnershipFacilitiesUsed($qualityPartnershipFacilitiesUsed);
        $this->setIsQualityContract($isQualityContract);
        $this->setQualityContractDetails($qualityContractDetails);

        return true;
    }

    /**
     * Update TA Authority
     *
     * @param string $stoppingArrangements Stopping arrangements
     *
     * @return bool
     */
    public function updateTaAuthority($stoppingArrangements)
    {
        $this->canEdit();

        $this->stoppingArrangements = $stoppingArrangements;

        return true;
    }

    public function updateServiceDetails(
        ?string $serviceNo,
        ?string $startPoint,
        ?string $finishPoint,
        ?string $via,
        ?string $otherDetails,
        ?string $receivedDate,
        ?string $effectiveDate,
        ?string $endDate,
        ?string $applicationCompleteDate,
        BusNoticePeriodEntity $busNoticePeriod
    ): true {
        $this->canEdit();

        $this->serviceNo = $serviceNo;
        $this->startPoint = $startPoint;
        $this->finishPoint = $finishPoint;
        $this->via = $via;
        $this->otherDetails = $otherDetails;
        $this->busNoticePeriod = $busNoticePeriod;

        $this->receivedDate = $receivedDate;
        $this->effectiveDate = $effectiveDate;
        $this->endDate = $endDate;
        $this->applicationCompleteDate = $applicationCompleteDate;
        $this->populateShortNotice();

        return true;
    }

    /**
     * Update service register
     *
     * @param string $trcConditionChecked Trc condition checked
     * @param string $trcNotes            Trc notes
     * @param string $copiedToLaPte       Copied to la pte
     * @param string $laShortNote         La short note
     * @param string $opNotifiedLaPte     Op notified la pte
     * @param string $applicationSigned   Application signed
     * @param string $timetableAcceptable Timetable acceptable
     * @param string $mapSupplied         Map supplied
     * @param string $routeDescription    Route description
     *
     * @return BusReg
     * @throws ForbiddenException
     */
    public function updateServiceRegister(
        $trcConditionChecked,
        $trcNotes,
        $copiedToLaPte,
        $laShortNote,
        $opNotifiedLaPte,
        $applicationSigned,
        $timetableAcceptable = null,
        $mapSupplied = null,
        $routeDescription = null
    ) {
        if (!$this->isLatestVariation()) {
            throw new ForbiddenException(self::FORBIDDEN_NO_PERMISSION_ERROR);
        }

        $this->trcConditionChecked = $trcConditionChecked;
        $this->trcNotes = $trcNotes;
        $this->copiedToLaPte = $copiedToLaPte;
        $this->laShortNote = $laShortNote;
        $this->opNotifiedLaPte = $opNotifiedLaPte;
        $this->applicationSigned = $applicationSigned;

        if ($timetableAcceptable !== null) {
            $this->timetableAcceptable = $timetableAcceptable;
        }

        if ($mapSupplied !== null) {
            $this->mapSupplied = $mapSupplied;
        }

        if ($routeDescription !== null) {
            $this->routeDescription = $routeDescription;
        }

        return $this;
    }

    /**
     * Is short notice
     *
     * @param \DateTime|null        $effectiveDate Effective date
     * @param \DateTime|null        $receivedDate  Received date
     * @param BusNoticePeriodEntity $busRules      Bus rules
     *
     * @return bool|null
     */
    private function isShortNotice($effectiveDate, $receivedDate, BusNoticePeriodEntity $busRules)
    {
        if (!($effectiveDate instanceof \DateTime) || !($receivedDate instanceof \DateTime)) {
            return false;
        }

        //we only check the parent for scottish variations/cancellations
        if ($busRules->isScottishRules() && $this->variationNo > 0) {
            //there should always be a parent, but we'll test for bad (most probably legacy) data
            if (!$this->parent instanceof BusReg || $this->parent->getEffectiveDate() === null) {
                return false;
            }

            $parentEffectiveDate = $this->parent->getEffectiveDate();

            $lastDateTime
                = ($parentEffectiveDate instanceof \DateTime)
                ? clone $parentEffectiveDate : new \DateTime($parentEffectiveDate);

            $interval = new \DateInterval('P' . $busRules->getCancellationPeriod() . 'D');

            //this rule applies until the day AFTER the cancellation period
            //so for a 90 day cancellation period, short notice applies until the 91st day
            if ($effectiveDate->setTime(0, 0) <= $lastDateTime->add($interval)->setTime(0, 0)) {
                return true;
            }
        }

        $receivedDate = clone $receivedDate;
        $interval = new \DateInterval('P' . $busRules->getStandardPeriod() . 'D');

        //non-scottish: this rule applies UNTIL the standard period day.
        //so for a 56 day standard period, the rule applies up to and including the 55th day
        if ($effectiveDate->setTime(0, 0) < $receivedDate->add($interval)->setTime(0, 0)) {
            return true;
        }

        return false;
    }

    /**
     * Populates the short notice field
     *
     * @return void
     */
    public function populateShortNotice()
    {
        $this->isShortNotice = 'N';

        $effectiveDate = $this->processDate($this->effectiveDate);
        $receivedDate = $this->processDate($this->receivedDate);

        if ($this->isShortNotice($effectiveDate, $receivedDate, $this->busNoticePeriod)) {
            $this->isShortNotice = 'Y';
        }
    }

    /**
     * Returns whether the record is short notice refused
     *
     * @return bool
     */
    public function isShortNoticeRefused()
    {
        return ($this->shortNoticeRefused === 'Y' ? true : false);
    }

    /**
     * Get decision
     *
     * @return array|null
     */
    public function getDecision()
    {
        $decisionTaken = true;
        $reason = null;

        switch ($this->status->getId()) {
            case self::STATUS_REFUSED:
                $reason = ($this->isShortNoticeRefused()) ? $this->reasonSnRefused : $this->reasonRefused;
                break;
            case self::STATUS_CANCELLED:
            case self::STATUS_ADMIN:
                $reason = $this->reasonCancelled;
                break;
            case self::STATUS_WITHDRAWN:
                if ($this->withdrawnReason !== null) {
                    $reason = $this->withdrawnReason->getDescription();
                }
                break;
            default:
                $decisionTaken = false;
                break;
        }

        return ($decisionTaken) ? [
            'decision' => $this->status->getDescription(),
            'reason' => $reason,
        ] : null;
    }

    /**
     * Returns whether a bus reg may be granted
     *
     * @return bool
     */
    public function isGrantable()
    {
        if (!in_array($this->status->getId(), array_keys(self::$grantStatusMap), true)) {
            return false;
        };

        if (false === $this->isGrantableBasedOnRequiredFields()) {
            // bus reg without all required fields which makes it non-grantable
            return false;
        }

        if (false === $this->isGrantableBasedOnShortNotice()) {
            // bus reg without short notice details or with one which makes it non-grantable
            return false;
        }

        if ($this->hasOutstandingFee()) {
            // bus reg with a fee which makes it non-grantable
            return false;
        }

        return true;
    }

    /**
     * Returns whether a bus reg has all required fields which makes it grantable
     *
     * @return bool
     */
    private function isGrantableBasedOnRequiredFields()
    {
        // mandatory fields which needs to be marked as Yes
        $yesFields = [
            'trcConditionChecked',
            'copiedToLaPte',
            'applicationSigned'
        ];

        if (!$this->isCancellation()) {
            // cancellations don't require a map or timetable, all other types do
            $yesFields[] = 'timetableAcceptable';
            $yesFields[] = 'mapSupplied';
        }

        if ($this->isScottishRules()) {
            // for Scottish short notice registrations opNotifiedLaPte is required
            $yesFields[] = 'opNotifiedLaPte';
        }

        foreach ($yesFields as $field) {
            if (empty($this->$field) || $this->$field !== 'Y') {
                return false;
            }
        }

        // mandatory fields which can't be empty
        $nonEmptyFields = [
            'applicationCompleteDate',
            'effectiveDate',
            'receivedDate',
            'serviceNo',
            'startPoint',
            'finishPoint',
        ];

        foreach ($nonEmptyFields as $field) {
            if (is_null($this->$field) || $this->$field === "") {
                return false;
            }
        }

        // mandatory collections which can't be empty
        $nonEmptyCollections = [
            'busServiceTypes',
            'trafficAreas',
            'localAuthoritys',
        ];

        foreach ($nonEmptyCollections as $field) {
            if (($this->$field === null) || $this->$field->isEmpty()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns whether a bus reg has short notice details which makes it grantable
     *
     * @return bool
     */
    private function isGrantableBasedOnShortNotice()
    {
        if ($this->isShortNotice !== 'Y') {
            // not a short notice one
            return true;
        }

        if (empty($this->shortNotice)) {
            // no short notice details makes it non-grantable
            return false;
        }

        if ($this->laShortNote !== 'Y') {
            return false;
        }

        return $this->shortNotice->hasGrantableDetails();
    }

    /**
     * Whether the registration has an active fee (anything except cancelled)
     *
     * @return bool
     */
    private function hasActiveFee()
    {
        $fees = $this->getFees();

        /** @var FeeEntity $fee */
        foreach ($fees as $fee) {
            if ($fee->isPaid() || $fee->isOutstanding()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Whether the registration has an outstanding fee
     *
     * @return bool
     */
    private function hasOutstandingFee()
    {
        $fees = $this->getFees();

        /** @var FeeEntity $fee */
        foreach ($fees as $fee) {
            if ($fee->isOutstanding()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns whether we should create a fee
     * (we need there to be a received date, have the correct status, and not already have an active fee)
     *
     * @return bool
     */
    public function shouldCreateFee()
    {
        if ($this->receivedDate !== null && $this->isChargeableStatus() && !$this->hasActiveFee()) {
            return true;
        }

        return false;
    }

    /**
     * Update status
     *
     * @param RefData $status Status
     *
     * @return BusReg
     */
    private function updateStatus(RefData $status)
    {
        $this->setRevertStatus($this->getStatus());
        $this->setStatus($status);
        $this->setStatusChangeDate(new \DateTime());

        return $this;
    }

    /**
     * Resets status
     *
     * @return BusReg
     */
    public function resetStatus()
    {
        $this->canMakeDecision();

        $this->updateStatus($this->getRevertStatus());

        return $this;
    }

    /**
     * Admin cancel
     *
     * @param RefData $status Status
     * @param string  $reason Reason
     *
     * @throws BadRequestException
     * @return BusReg
     */
    public function cancelByAdmin(RefData $status, $reason)
    {
        $this->canMakeDecision();

        if ($status->getId() !== self::STATUS_ADMIN) {
            throw new BadRequestException('Please provide a valid status');
        }

        $this->updateStatus($status);
        $this->setReasonCancelled($reason);

        return $this;
    }

    /**
     * Withdraw
     *
     * @param RefData $status Status
     * @param RefData $reason Reason
     *
     * @throws BadRequestException
     * @return BusReg
     */
    public function withdraw(RefData $status, RefData $reason)
    {
        $this->canMakeDecision();

        if ($status->getId() !== self::STATUS_WITHDRAWN) {
            throw new BadRequestException('Please provide a valid status');
        }

        $this->updateStatus($status);
        $this->setWithdrawnReason($reason);

        return $this;
    }

    /**
     * Refuse
     *
     * @param RefData $status Status
     * @param string  $reason Reason
     *
     * @throws BadRequestException
     * @return BusReg
     */
    public function refuse(RefData $status, $reason)
    {
        $this->canMakeDecision();

        if ($status->getId() !== self::STATUS_REFUSED) {
            throw new BadRequestException('Please provide a valid status');
        }

        $this->updateStatus($status);
        $this->setReasonRefused($reason);

        return $this;
    }

    /**
     * Refuse by Short Notice
     *
     * @param string $reason Reason
     *
     * @return BusReg
     */
    public function refuseByShortNotice($reason)
    {
        $this->canMakeDecision();

        $this->setShortNoticeRefused('Y');
        $this->setReasonSnRefused($reason);
        $this->setEffectiveDate($this->calculateNoticeDate());

        // reset the short notice record
        $this->setIsShortNotice('N');

        if ($this->getShortNotice() !== null) {
            $this->getShortNotice()->reset();
        }

        return $this;
    }

    /**
     * Calculates the short notice date
     *
     * @return \DateTime|null
     */
    private function calculateNoticeDate()
    {
        $receivedDateTime = $this->processDate($this->receivedDate);

        if (!($receivedDateTime instanceof \DateTime)) {
            return null;
        }

        //first try to work out the date based on the standard period: : this rule applies UNTIL the standard period
        //day. For a 56 day standard period, the rule applies up to and including the 55th day
        $standardPeriodInterval = new \DateInterval('P' . ($this->busNoticePeriod->getStandardPeriod()) . 'D');
        $standardPeriodDate = $receivedDateTime->add($standardPeriodInterval);

        //we only check the parent for scottish variations/cancellations
        //there should always be a parent, but we'll test for bad (most probably legacy) data
        if (
            $this->busNoticePeriod->isScottishRules()
            && $this->variationNo > 0
            && $this->parent instanceof BusReg
            && $this->parent->getEffectiveDate() !== null
        ) {
            $parentEffectiveDate = $this->processDate($this->parent->getEffectiveDate());

            //use the cancellation period, these can only be granted AFTER the cancellation period has passed, so for a
            //90 day cancellation period, the rule applies until the 91st day
            $parentInterval = new \DateInterval('P' . ($this->busNoticePeriod->getCancellationPeriod() + 1) . 'D');
            $parentPeriodDate = $parentEffectiveDate->add($parentInterval);

            //if the date we got from the parent is later than what we got from the standard period, use the parent
            if ($parentPeriodDate > $standardPeriodDate) {
                return $parentPeriodDate;
            }
        }

        return $standardPeriodDate;
    }

    /**
     * Grant
     *
     * @param RefData         $status           Status
     * @param ArrayCollection $variationReasons Variation reasons
     *
     * @throws BadRequestException
     * @return BusReg
     */
    public function grant(RefData $status, $variationReasons = null)
    {
        $this->canMakeDecision();

        if ($this->isGrantable() !== true) {
            throw new BadRequestException('The Bus Reg is not grantable');
        }

        if ($status->getId() !== $this->getStatusForGrant()) {
            throw new BadRequestException('The Bus Reg is not grantable');
        }

        if ($this->status->getId() === self::STATUS_VAR) {
            $this->setVariationReasons($variationReasons);
        }

        $this->updateStatus($status);

        return $this;
    }

    /**
     * Get status for grant action
     *
     * @return string
     */
    public function getStatusForGrant()
    {
        return (!empty(self::$grantStatusMap[$this->status->getId()]))
            ? self::$grantStatusMap[$this->status->getId()] : null;
    }

    /**
     * Get context value
     *
     * @return string
     */
    public function getContextValue()
    {
        return $this->getLicence()->getLicNo();
    }

    /**
     * Add other service number
     *
     * @param string $serviceNo Service No
     *
     * @return void
     */
    public function addOtherServiceNumber($serviceNo)
    {
        $otherServiceEntity = new BusRegOtherService($this, $serviceNo);
        $this->otherServices->add($otherServiceEntity);
    }

    /**
     * Gets the publication section for a grant/cancellation email
     *
     * @return int
     * @throws RuntimeException
     */
    public function getPublicationSectionForGrantEmail()
    {
        $currentStatus = $this->status->getId();
        $allowableCurrentStatus = [self::STATUS_REGISTERED, self::STATUS_CANCELLED];

        if (!in_array($currentStatus, $allowableCurrentStatus)) {
            throw new RuntimeException('valid statuses for grant email are registered and cancelled');
        }

        $revertStatus = $this->revertStatus->getId();
        $shortNotice = $this->isShortNotice;
        return match ($revertStatus) {
            self::STATUS_NEW, self::STATUS_VAR => $this->getPublicationSectionForGrantEmailRegistered($revertStatus, $shortNotice),
            self::STATUS_CANCEL => $this->getPublicationSectionForGrantEmailCancelled($shortNotice),
            default => throw new RuntimeException('valid revert statuses for grant email are new, variation or cancellation'),
        };
    }

    /**
     * Get publication section for grant email registered
     *
     * @param string $revertStatus Revert status
     * @param string $shortNotice  Short notice
     *
     * @return int
     * @throws RuntimeException
     */
    private function getPublicationSectionForGrantEmailRegistered($revertStatus, $shortNotice)
    {
        if (!$this->isRegistered()) {
            throw new RuntimeException('status mismatch generating registered grant email');
        }

        if ($revertStatus === self::STATUS_NEW) {
            return ($shortNotice == 'Y' ?
                PublicationSectionEntity::BUS_NEW_SHORT_SECTION :
                PublicationSectionEntity::BUS_NEW_SECTION
            );
        } else {
            return ($shortNotice == 'Y' ?
                PublicationSectionEntity::BUS_VAR_SHORT_SECTION :
                PublicationSectionEntity::BUS_VAR_SECTION
            );
        }
    }

    /**
     * Get publication section for grant email cancelled
     *
     * @param string $shortNotice Short notice
     *
     * @return int
     * @throws RuntimeException
     */
    private function getPublicationSectionForGrantEmailCancelled($shortNotice)
    {
        if ($this->status->getId() !== self::STATUS_CANCELLED) {
            throw new RuntimeException('status mismatch generating cancellation grant email');
        }

        return ($shortNotice == 'Y' ?
            PublicationSectionEntity::BUS_CANCEL_SHORT_SECTION :
            PublicationSectionEntity::BUS_CANCEL_SECTION
        );
    }

    /**
     * Gets a string of publications affected by a grant action, called by the EBSR emails,
     * only relevant for certain emails, so often will return empty string
     *
     * @param PublicationSectionEntity $pubSection Pub section
     *
     * @return string
     */
    public function getPublicationLinksForGrantEmail(PublicationSectionEntity $pubSection)
    {
        $expr = Criteria::expr();
        $criteria = Criteria::create();

        $criteria->where($expr->eq('publicationSection', $pubSection));

        $matchingLinks = $this->publicationLinks->matching($criteria);
        $matchingPublications = [];

        /** @var PublicationLinkEntity $link */
        foreach ($matchingLinks as $link) {
            $matchedPublication = $link->getPublication();

            //only include new publications, and ignore duplicates by having pubNo as the array key
            if ($matchedPublication->isNew()) {
                $pubNo = $matchedPublication->getPublicationNo();
                $pubTa = $matchedPublication->getTrafficArea()->getName();
                $matchingPublications[$pubNo] = $pubNo . ' ' . $pubTa;
            }
        }

        return implode(', ', $matchingPublications);
    }

    /**
     * Return a list of service numbers formatted with pattern: serviceNo(serviceNo2,serviceNo3,serviceNo4)
     *
     * @return string
     */
    public function getFormattedServiceNumbers()
    {
        $additional = [];

        /** @var BusRegOtherServiceEntity $otherService */
        foreach ($this->otherServices as $otherService) {
            $additional[] = $otherService->getServiceNo();
        }
        //strip out any empty string values except 0
        $additional = array_filter($additional, strlen(...));

        if (!empty($additional)) {
            return $this->serviceNo . '(' . implode(',', $additional) . ')';
        }

        return $this->serviceNo;
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation|null
     */
    public function getRelatedOrganisation()
    {
        if ($this->getLicence()) {
            return $this->getLicence()->getRelatedOrganisation();
        }

        return null;
    }

    /**
     * Flag for raising fees. Based on the current status.
     *
     * @note Be careful where in the code this is called. The current status determines whether a fee is generated so
     * ensure it is called at the appropriate point in the code. ie. with the new status being applied.
     *
     * @return bool
     */
    public function isChargeableStatus()
    {
        $busStatus = $this->getStatus()->getId();

        if ($busStatus === self::STATUS_NEW || $busStatus === self::STATUS_VAR) {
            return true;
        }

        return false;
    }

    /**
     * Returns the ids of an open tasks
     *
     * @return array
     */
    public function getOpenTaskIds()
    {
        $taskIds = [];

        /** @var TaskEntity $task */
        foreach ($this->tasks as $task) {
            if ($task->getIsClosed() !== 'Y') {
                $taskIds[] = $task->getId();
            }
        }

        return $taskIds;
    }

    /**
     * Determine if this Registration can be withdrawn
     *
     * @return bool
     */
    public function canWithdraw()
    {
        return in_array(
            $this->status->getId(),
            [self::STATUS_NEW, self::STATUS_VAR, self::STATUS_CANCEL],
            true
        );
    }

    /**
     * Determine if registration can be refused
     *
     * @return bool
     */
    public function canRefuse()
    {
        return in_array(
            $this->status->getId(),
            [self::STATUS_NEW, self::STATUS_VAR, self::STATUS_CANCEL],
            true
        );
    }

    /**
     * Determine if registration can be refused by short notice
     *
     * @return bool
     */
    public function canRefuseByShortNotice()
    {
        return $this->canRefuse()
            && $this->isShortNotice === 'Y'
            && $this->shortNoticeRefused === 'N';
    }

    /**
     * Determine if a cancellation can be created
     *
     * @return bool
     */
    public function canCreateCancellation()
    {
        return $this->status->getId() === self::STATUS_REGISTERED
            && $this->isLatestVariation();
    }

    /**
     * Determine if registration can be printed
     *
     * @return bool
     */
    public function canPrint()
    {
        return in_array($this->status->getId(), [self::STATUS_REGISTERED, self::STATUS_CANCELLED], true);
    }

    /**
     * Determine if a new route map can be requested
     *
     * @return bool
     */
    public function canRequestNewRouteMap()
    {
        return $this->isFromEbsr();
    }

    /**
     * Determine if a registration can be republished
     *
     * @return bool
     */
    public function canRepublish()
    {
        return in_array($this->status->getId(), [self::STATUS_REGISTERED, self::STATUS_CANCELLED], true)
            && $this->isLatestVariation();
    }

    /**
     * Determine if registration can be admin-cancelled
     *
     * @return bool
     */
    public function canCancelByAdmin()
    {
        return $this->status->getId() === self::STATUS_REGISTERED
            && $this->isLatestVariation();
    }

    /**
     * Determine if registration can be reset
     *
     * @return bool
     */
    public function canResetRegistration()
    {
        return !in_array($this->status->getId(), [self::STATUS_NEW, self::STATUS_VAR, self::STATUS_CANCEL], true)
            && $this->isLatestVariation();
    }
}
