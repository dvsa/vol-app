<?php

namespace Dvsa\Olcs\Api\Entity\Irfo;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\OrganisationProviderInterface;

/**
 * IrfoPsvAuth Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="irfo_psv_auth",
 *    indexes={
 *        @ORM\Index(name="ix_irfo_psv_auth_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_journey_frequency", columns={"journey_frequency"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_irfo_psv_auth_type_id", columns={"irfo_psv_auth_type_id"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_status", columns={"status"}),
 *        @ORM\Index(name="ix_irfo_psv_auth_withdrawn_reason", columns={"withdrawn_reason"})
 *    }
 * )
 */
class IrfoPsvAuth extends AbstractIrfoPsvAuth implements OrganisationProviderInterface
{
    public const STATUS_APPROVED = 'irfo_auth_s_approved';
    public const STATUS_CNS = 'irfo_auth_s_cns';
    public const STATUS_GRANTED = 'irfo_auth_s_granted';
    public const STATUS_PENDING = 'irfo_auth_s_pending';
    public const STATUS_REFUSED = 'irfo_auth_s_refused';
    public const STATUS_RENEW = 'irfo_auth_s_renew';
    public const STATUS_WITHDRAWN = 'irfo_auth_s_withdrawn';

    public const JOURNEY_FREQ_DAILY = 'psv_freq_daily';
    public const JOURNEY_FREQ_TWICE_WEEKLY = 'psv_freq_2_weekly';
    public const JOURNEY_FREQ_WEEKLY = 'psv_freq_weekly';
    public const JOURNEY_FREQ_FORTNIGHTLY = 'psv_freq_fortnight';
    public const JOURNEY_FREQ_MONTHLY = 'psv_freq_monthly';
    public const JOURNEY_FREQ_OTHER = 'psv_freq_other';

    public function __construct(Organisation $organisation, IrfoPsvAuthType $type, RefData $status)
    {
        $this->organisation = $organisation;
        $this->irfoPsvAuthType = $type;
        $this->status = $status;

        parent::__construct();
    }

    /**
     * Update
     *
     * @param $validityPeriod
     * @param \DateTime $inForceDate
     * @param $serviceRouteFrom
     * @param $serviceRouteTo
     * @param $copiesRequired
     * @param $copiesRequiredTotal
     * @return $this
     */
    public function update(
        IrfoPsvAuthType $type,
        $validityPeriod,
        \DateTime $inForceDate,
        $serviceRouteFrom,
        $serviceRouteTo,
        RefData $journeyFrequency,
        $copiesRequired,
        $copiesRequiredTotal
    ) {
        $this->irfoPsvAuthType = $type;
        $this->validityPeriod = $validityPeriod;
        $this->inForceDate = $inForceDate;
        $this->serviceRouteFrom = $serviceRouteFrom;
        $this->serviceRouteTo = $serviceRouteTo;
        $this->journeyFrequency = $journeyFrequency;
        $this->copiesRequired = $copiesRequired;
        $this->copiesRequiredTotal = $copiesRequiredTotal;

        // deal with IrfoFileNo
        $this->populateFileNo();

        return $this;
    }

    /**
     * Populate File Number
     * @return IrfoPsvAuth
     */
    public function populateFileNo()
    {
        $irfoFileNo = sprintf(
            '%s/%d',
            $this->getIrfoPsvAuthType()->getSectionCode(),
            $this->getId()
        );
        $this->setIrfoFileNo($irfoFileNo);

        return $this;
    }

    /**
     * Populate Irfo Fee Id
     * IR& {0,to pad to 7 chars} &[operator ID] eg "IR0001867"
     *
     * @return IrfoPsvAuth
     */
    public function populateIrfoFeeId()
    {
        $irfoFeeId = 'IR' . str_pad($this->getOrganisation()->getId(), 7, '0', STR_PAD_LEFT);

        $this->setIrfoFeeId($irfoFeeId);

        return $this;
    }

    /**
     * Is paid for? Yes if application fee exists and is paid or waived
     *
     * @param null $applicationFeeStatusId
     * @return bool
     */
    private function isPaidFor($applicationFeeStatusId = null)
    {
        return ($applicationFeeStatusId === Fee::STATUS_PAID);
    }

    /**
     * Is in a grantable state
     *
     * @return bool
     */
    private function isGrantableState()
    {
        if (in_array($this->getStatus()->getId(), [self::STATUS_RENEW, self::STATUS_PENDING])) {
            return true;
        }

        return false;
    }

    /**
     * Is grantable? Yes if entity status is renew/pending, and application fee exists and is paid or waived
     *
     * @param null $applicationFee
     * @return bool
     */
    public function isGrantable($applicationFee = null)
    {
        if ($applicationFee instanceof Fee) {
            $applicationFeeStatusId = $applicationFee->getFeeStatus()->getId();

            if ($this->isPaidFor($applicationFeeStatusId) && $this->isGrantableState()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Grant
     *
     * @return $this
     * @throws BadRequestException
     */
    public function grant(RefData $status, Fee $applicationFee)
    {
        if (!$this->isGrantable($applicationFee)) {
            throw new BadRequestException(
                'Irfo Psv Auth is not grantable'
            );
        }

        $this->setStatus($status);

        return $this;
    }

    /**
     * Is generatable?
     *
     * @param array $outstandingFees
     * @return bool
     */
    public function isGeneratable($outstandingFees)
    {
        return ($this->isGeneratableState() && empty($outstandingFees));
    }

    /**
     * Is in a generatable state
     *
     * @return bool
     */
    private function isGeneratableState()
    {
        return ($this->getStatus()->getId() === self::STATUS_APPROVED);
    }

    /**
     * Generate
     *
     * @param array $outstandingFees
     * @return $this
     * @throws BadRequestException
     */
    public function generate($outstandingFees)
    {
        if (!$this->isGeneratable($outstandingFees)) {
            throw new BadRequestException(
                'Irfo Psv Auth is not generatable'
            );
        }

        // update copies issued
        $this->copiesIssued += (int)$this->copiesRequired;
        $this->copiesIssuedTotal += (int)$this->copiesRequiredTotal;

        // reset copies required
        $this->copiesRequired = 0;
        $this->copiesRequiredTotal = 0;

        return $this;
    }

    /**
     * Is in an approvable state
     *
     * @return bool
     */
    private function isApprovableState()
    {
        return ($this->getStatus()->getId() === self::STATUS_GRANTED);
    }

    /**
     * Is approvable?
     *
     * @param array $outstandingFees
     * @return bool
     */
    public function isApprovable($outstandingFees)
    {
        return ($this->isApprovableState() && empty($outstandingFees));
    }

    /**
     * Approve
     *
     * @param array $outstandingFees
     * @return $this
     * @throws BadRequestException
     */
    public function approve(RefData $status, $outstandingFees)
    {
        if (!$this->isApprovable($outstandingFees)) {
            throw new BadRequestException(
                'Irfo Psv Auth is not approvable'
            );
        }

        $this->setStatus($status);

        // set renewal date to today's date
        $this->setRenewalDate(new DateTime());

        return $this;
    }

    /**
     * Is refusable? Yes if entity status is renew/pending
     *
     * @return bool
     */
    public function isRefusable()
    {
        if ($this->isRefusableState()) {
            return true;
        }

        return false;
    }

    /**
     * Is in a refusable state
     *
     * @return bool
     */
    private function isRefusableState()
    {
        if (in_array($this->getStatus()->getId(), [self::STATUS_RENEW, self::STATUS_PENDING])) {
            return true;
        }

        return false;
    }

    /**
     * Refuse
     *
     * @return $this
     * @throws BadRequestException
     */
    public function refuse(RefData $status)
    {
        if (!$this->isRefusable()) {
            throw new BadRequestException(
                'Irfo Psv Auth is not refusable'
            );
        }

        $this->setStatus($status);

        return $this;
    }

    /**
     * Is withdrawable? Yes if entity status is renew/pending/cns/approved
     *
     * @return bool
     */
    public function isWithdrawable()
    {
        if ($this->isWithdrawableState()) {
            return true;
        }

        return false;
    }

    /**
     * Is in a withdrawable state. Yes if entity status is renew/pending/cns/approved
     *
     * @return bool
     */
    private function isWithdrawableState()
    {
        if (
            in_array(
                $this->getStatus()->getId(),
                [
                self::STATUS_RENEW,
                self::STATUS_PENDING,
                self::STATUS_CNS,
                self::STATUS_APPROVED
                ]
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Withdraw
     *
     * @return $this
     * @throws BadRequestException
     */
    public function withdraw(RefData $status)
    {
        if (!$this->isWithdrawable()) {
            throw new BadRequestException(
                'Irfo Psv Auth is not withdrawable'
            );
        }

        $this->setStatus($status);

        return $this;
    }

    /**
     * Is resetable? Yes if entity status is not pending
     *
     * @return bool
     */
    public function isResetable()
    {
        return $this->isResetableState();
    }

    /**
     * Is in a resetable state. Always
     *
     * @return bool
     */
    private function isResetableState()
    {
        if ($this->getStatus()->getId() !== self::STATUS_PENDING) {
            return true;
        }

        return false;
    }

    /**
     * Reset
     *
     * @return $this
     * @throws BadRequestException
     */
    public function reset(RefData $status)
    {
        if (!$this->isResetable()) {
            throw new BadRequestException(
                'Irfo Psv Auth cannot be reset'
            );
        }

        $this->setStatus($status);

        return $this;
    }

    /**
     * Is CNSable?
     *
     * @return bool
     */
    public function isCnsable()
    {
        return $this->isCnsableState();
    }

    /**
     * Is in a CNSable state.
     *
     * @return bool
     */
    private function isCnsableState()
    {
        if ($this->getStatus()->getId() === self::STATUS_RENEW) {
            return true;
        }

        return false;
    }

    /**
     * Continuation Not Sought
     *
     * @return $this
     * @throws BadRequestException
     */
    public function continuationNotSought(RefData $status)
    {
        if (!$this->isCnsable()) {
            throw new BadRequestException(
                'Irfo Psv Auth cannot be set as CNS'
            );
        }

        $this->setStatus($status);

        return $this;
    }

    /**
     * Is renewable?
     *
     * @return bool
     */
    public function isRenewable()
    {
        return $this->isRenewableState();
    }

    /**
     * Is in a renewable state
     *
     * @return bool
     */
    private function isRenewableState()
    {
        if (
            in_array(
                $this->getStatus()->getId(),
                [
                self::STATUS_APPROVED,
                self::STATUS_GRANTED,
                self::STATUS_PENDING,
                self::STATUS_RENEW,
                ]
            )
        ) {
            return true;
        }

        return false;
    }

    /**
     * Renew
     *
     * @return $this
     * @throws BadRequestException
     */
    public function renew(RefData $status)
    {
        if (!$this->isRenewable()) {
            throw new BadRequestException(
                'Irfo Psv Auth cannot be renewed'
            );
        }

        $this->setStatus($status);

        return $this;
    }

    /**
     * Get organisations this entity is linked to
     *
     * @return \Dvsa\Olcs\Api\Entity\Organisation\Organisation
     */
    public function getRelatedOrganisation()
    {
        return $this->getOrganisation();
    }
}
