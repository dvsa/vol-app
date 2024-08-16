<?php

namespace Dvsa\Olcs\Api\Entity\Permits;

use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * IrhpPermit Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irhp_permit",
 *    indexes={
 *        @ORM\Index(name="fk_irhp_permits_irhp_permit_range_idx", columns={"irhp_permit_range_id"}),
 *        @ORM\Index(name="fk_irhp_permits_irhp_permit_application1_idx",
     *     columns={"irhp_permit_application_id"}),
 *        @ORM\Index(name="fk_irhp_permits_irhp_candidate_permit1_idx",
     *     columns={"irhp_candidate_permit_id"}),
 *        @ORM\Index(name="fk_irhp_permit_created_by_user_id", columns={"created_by"}),
 *        @ORM\Index(name="fk_irhp_permit_last_modified_by_user_id", columns={"last_modified_by"}),
 *        @ORM\Index(name="fk_irhp_permit_status_ref_data_id", columns={"status"})
 *    }
 * )
 */
class IrhpPermit extends AbstractIrhpPermit
{
    public const STATUS_PENDING            = 'irhp_permit_pending';
    public const STATUS_AWAITING_PRINTING  = 'irhp_permit_awaiting_printing';
    public const STATUS_PRINTING           = 'irhp_permit_printing';
    public const STATUS_PRINTED            = 'irhp_permit_printed';
    public const STATUS_ERROR              = 'irhp_permit_error';
    public const STATUS_CEASED             = 'irhp_permit_ceased';
    public const STATUS_TERMINATED         = 'irhp_permit_terminated';
    public const STATUS_EXPIRED            = 'irhp_permit_expired';

    public const ALL_STATUSES = [
        self::STATUS_PENDING,
        self::STATUS_AWAITING_PRINTING,
        self::STATUS_PRINTING,
        self::STATUS_PRINTED,
        self::STATUS_ERROR,
        self::STATUS_CEASED,
        self::STATUS_TERMINATED,
        self::STATUS_EXPIRED,
    ];

    public static $readyToPrintStatuses = [
        self::STATUS_PENDING,
        self::STATUS_AWAITING_PRINTING,
        self::STATUS_PRINTING,
        self::STATUS_ERROR,
    ];

    public static $validStatuses = [
        self::STATUS_PENDING,
        self::STATUS_AWAITING_PRINTING,
        self::STATUS_ERROR,
        self::STATUS_PRINTING,
        self::STATUS_PRINTED,
    ];

    /**
     * Create new IrhpPermit
     *
     * @param int                   $permitNumber
     *
     * @return IrhpPermit
     */
    public static function createNew(
        IrhpCandidatePermit $irhpCandidatePermit,
        DateTime $issueDate,
        RefData $status,
        $permitNumber,
        ?DateTime $expiryDate
    ) {
        $irhpPermit = new self();
        $irhpPermit->irhpCandidatePermit = $irhpCandidatePermit;
        $irhpPermit->irhpPermitApplication = $irhpCandidatePermit->getIrhpPermitApplication();
        $irhpPermit->irhpPermitRange = $irhpCandidatePermit->getIrhpPermitRange();
        $irhpPermit->issueDate = $issueDate;
        $irhpPermit->status = $status;
        $irhpPermit->permitNumber = $permitNumber;
        $irhpPermit->expiryDate = $expiryDate;

        return $irhpPermit;
    }

    /**
     * Create new IrhpPermit
     *
     * @param int $permitNumber
     *
     * @return IrhpPermit
     */
    public static function createReplacement(
        IrhpPermit $oldPermit,
        IrhpPermitRange $irhpPermitRange,
        RefData $status,
        $permitNumber
    ) {
        $expiryDate = $oldPermit
            ->getExpiryDate() ?: $oldPermit->getIrhpPermitRange()->getIrhpPermitStock()->getValidTo();

        $irhpPermit = new self();
        $irhpPermit->replaces = $oldPermit;
        $irhpPermit->irhpCandidatePermit = $oldPermit->getIrhpCandidatePermit();
        $irhpPermit->irhpPermitApplication = $oldPermit->getIrhpPermitApplication();
        $irhpPermit->irhpPermitRange = $irhpPermitRange;
        $irhpPermit->issueDate = new DateTime();
        $irhpPermit->expiryDate = $expiryDate;
        $irhpPermit->status = $status;
        $irhpPermit->permitNumber = $permitNumber;

        return $irhpPermit;
    }

    /**
     * Create new IrhpPermit during irhp permit application allocation
     *
     * @param int $permitNumber
     *
     * @return IrhpPermit
     */
    public static function createForIrhpApplication(
        IrhpPermitApplication $irhpPermitApplication,
        IrhpPermitRange $irhpPermitRange,
        DateTime $issueDate,
        RefData $status,
        $permitNumber,
        ?DateTime $expiryDate
    ) {
        $irhpPermit = new self();
        $irhpPermit->irhpPermitApplication = $irhpPermitApplication;
        $irhpPermit->irhpPermitRange = $irhpPermitRange;
        $irhpPermit->issueDate = $issueDate;
        $irhpPermit->status = $status;
        $irhpPermit->permitNumber = $permitNumber;
        $irhpPermit->expiryDate = $expiryDate;

        return $irhpPermit;
    }

    /**
     * @return $this
     */
    public function cease(RefData $status)
    {
        // Check status provided is correct for Ceased and set expiry date to now.
        if ($status->getId() !== self::STATUS_CEASED) {
            throw new ForbiddenException('This method can only be called with refdata status id: ' . self::STATUS_CEASED);
        }
        $this->status = $status;
        $this->expiryDate = new DateTime();

        return $this;
    }

    /**
     * Get the permit number with prefix
     *
     * @return string
     */
    public function getPermitNumberWithPrefix()
    {
        return sprintf('%s%05d', $this->getIrhpPermitRange()->getPrefix(), $this->getPermitNumber());
    }

    /**
     * Calculated values to be added to a bundle
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        return [
            'permitNumberWithPrefix' => $this->getPermitNumberWithPrefix(),
            'startDate' => $this->getStartDate(),
            'ceasedDate' => $this->getCeasedDate()
        ];
    }

    /**
     * Get the permit start date
     *
     * @return DateTime
     */
    public function getStartDate()
    {
        // set to stock's valid from date by default
        $startDate = $this->getIrhpPermitRange()->getIrhpPermitStock()->getValidFrom(true);

        $issueDate = $this->getIssueDate(true);

        if (isset($startDate) && isset($issueDate) && ($issueDate > $startDate)) {
            // overwrite with the issue date
            $startDate = $issueDate;
        }

        return $startDate;
    }

    /**
     * Proceed to status
     *
     * @param RefData $status Status
     *
     * @return void
     * @throws ForbiddenException
     */
    public function proceedToStatus(RefData $status)
    {
        match ($status->getId()) {
            self::STATUS_AWAITING_PRINTING => $this->proceedToAwaitingPrinting($status),
            self::STATUS_PRINTING => $this->proceedToPrinting($status),
            self::STATUS_PRINTED => $this->proceedToPrinted($status),
            self::STATUS_ERROR => $this->proceedToError($status),
            self::STATUS_TERMINATED => $this->proceedToTerminated($status),
            default => throw new ForbiddenException(sprintf('Action for status %s not defined.', $status->getId())),
        };
    }

    /**
     * Proceed to awaiting printing
     *
     * @param RefData $status Status
     *
     * @return void
     * @throws ForbiddenException
     */
    private function proceedToAwaitingPrinting(RefData $status)
    {
        if (!$this->isPending() && !$this->hasError()) {
            throw new ForbiddenException(
                sprintf(
                    'The permit is not in the correct state to proceed to awaiting printing (%s)',
                    $this->status->getId()
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Proceed to printing
     *
     * @param RefData $status Status
     *
     * @return void
     * @throws ForbiddenException
     */
    private function proceedToPrinting(RefData $status)
    {
        if (!$this->isAwaitingPrinting()) {
            throw new ForbiddenException(
                sprintf(
                    'The permit is not in the correct state to proceed to printing (%s)',
                    $this->status->getId()
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Proceed to printed
     *
     * @param RefData $status Status
     *
     * @return void
     * @throws ForbiddenException
     */
    private function proceedToPrinted(RefData $status)
    {
        if (!$this->isPrinting()) {
            throw new ForbiddenException(
                sprintf(
                    'The permit is not in the correct state to proceed to printed (%s)',
                    $this->status->getId()
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Proceed to error
     *
     * @param RefData $status Status
     *
     * @return void
     * @throws ForbiddenException
     */
    private function proceedToError(RefData $status)
    {
        if ($this->hasError()) {
            // do nothing if the status is STATUS_ERROR already
            return;
        }

        if (!$this->isPending() && !$this->isAwaitingPrinting() && !$this->isPrinting()) {
            throw new ForbiddenException(
                sprintf(
                    'The permit is not in the correct state to proceed to error (%s)',
                    $this->status->getId()
                )
            );
        }

        $this->status = $status;
    }

    /**
     * Proceed to terminated
     *
     * @param RefData $status Status
     *
     * @return void
     * @throws ForbiddenException
     */
    private function proceedToTerminated(RefData $status)
    {
        if ($this->isCeased() || $this->isTerminated() || $this->isExpired()) {
            throw new ForbiddenException(
                sprintf(
                    'The permit is not in the correct state to be terminated (%s)',
                    $this->status->getId()
                )
            );
        }
        $this->expiryDate = new DateTime();
        $this->status = $status;
    }

    /**
     * Is pending
     *
     * @return bool
     */
    public function isPending()
    {
        return $this->status->getId() === self::STATUS_PENDING;
    }

    /**
     * Is awaiting printing
     *
     * @return bool
     */
    public function isAwaitingPrinting()
    {
        return $this->status->getId() === self::STATUS_AWAITING_PRINTING;
    }

    /**
     * Is printing
     *
     * @return bool
     */
    public function isPrinting()
    {
        return $this->status->getId() === self::STATUS_PRINTING;
    }

    /**
     * Is printed
     *
     * @return bool
     */
    public function isPrinted()
    {
        return $this->status->getId() === self::STATUS_PRINTED;
    }

    /**
     * Has error
     *
     * @return bool
     */
    public function hasError()
    {
        return $this->status->getId() === self::STATUS_ERROR;
    }

    /**
     * Is not ceased
     *
     * @return bool
     */
    public function isCeased()
    {
        return $this->status->getId() === self::STATUS_CEASED;
    }

    /**
     * Is terminated
     *
     * @return bool
     */
    public function isTerminated()
    {
        return $this->status->getId() === self::STATUS_TERMINATED;
    }

    /**
     * Is expired
     *
     * @return bool
     */
    public function isExpired()
    {
        return $this->status->getId() === self::STATUS_EXPIRED;
    }

    /**
     * Is valid
     *
     * @return bool
     */
    public function isValid()
    {
        return in_array($this->status->getId(), self::$validStatuses);
    }

    /**
     * @return DateTime
     */
    public function getCeasedDate()
    {
        return $this->getExpiryDate() ?? $this->getIrhpPermitRange()->getIrhpPermitStock()->getValidTo(true);
    }

    /**
     * Regenerate the issue date and expiry date as appropriate for the associated permit type
     */
    public function regenerateIssueDateAndExpiryDate()
    {
        $irhpPermitApplication = $this->irhpPermitApplication;

        $this->expiryDate = $irhpPermitApplication->generateExpiryDate();
        $this->issueDate = $irhpPermitApplication->generateIssueDate();
    }
}
