<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc as PresidingTcEntity;
use Dvsa\Olcs\Api\Entity\Pi\Pi as PiEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\Venue as VenueEntity;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;

/**
 * PiHearing Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="pi_hearing",
 *    indexes={
 *        @ORM\Index(name="ix_pi_hearing_pi_id", columns={"pi_id"}),
 *        @ORM\Index(name="ix_pi_hearing_presiding_tc_id", columns={"presiding_tc_id"}),
 *        @ORM\Index(name="ix_pi_hearing_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_pi_hearing_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_pi_hearing_presided_by_role", columns={"presided_by_role"}),
 *        @ORM\Index(name="ix_pi_hearing_venue_id", columns={"venue_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_pi_hearing_olbs_key_olbs_type", columns={"olbs_key","olbs_type"})
 *    }
 * )
 */
class PiHearing extends AbstractPiHearing
{
    public const MSG_HEARING_DATE_BEFORE_PI_DATE = 'HEARING_DATE_BEFORE_PI';
    public const PUBLISH_AFTER_DAYS = 5;

    /**
     * @param VenueEntity|null  $venue
     * @param string            $venueOther
     * @param int               $witnesses
     * @param int               $drivers
     * @param string            $isCancelled
     * @param string            $cancelledDate
     * @param string            $cancelledReason
     * @param string            $isAdjourned
     * @param string            $adjournedDate
     * @param string            $adjournedReason
     * @param string            $details
     */
    public function __construct(
        PiEntity $pi,
        PresidingTcEntity $presidingTc,
        RefData $presidedByRole,
        \DateTime $hearingDate,
        $venue,
        $venueOther,
        $witnesses,
        $drivers,
        $isCancelled,
        $cancelledDate,
        $cancelledReason,
        $isAdjourned,
        $adjournedDate,
        $adjournedReason,
        $details
    ) {
        $this->create(
            $pi,
            $presidingTc,
            $presidedByRole,
            $hearingDate,
            $venue,
            $venueOther,
            $witnesses,
            $drivers,
            $isCancelled,
            $cancelledDate,
            $cancelledReason,
            $isAdjourned,
            $adjournedDate,
            $adjournedReason,
            $details
        );
    }

    /**
     * @param VenueEntity|null $venue
     * @param string           $venueOther
     * @param int              $witnesses
     * @param int              $drivers
     * @param string           $isCancelled
     * @param string           $cancelledDate
     * @param string           $cancelledReason
     * @param string           $isAdjourned
     * @param string           $adjournedDate
     * @param string           $adjournedReason
     * @param string           $details
     * @throws ForbiddenException
     * @return void
     */
    private function create(
        PiEntity $pi,
        PresidingTcEntity $presidingTc,
        RefData $presidedByRole,
        \DateTime $hearingDate,
        $venue,
        $venueOther,
        $witnesses,
        $drivers,
        $isCancelled,
        $cancelledDate,
        $cancelledReason,
        $isAdjourned,
        $adjournedDate,
        $adjournedReason,
        $details
    ) {
        if ($pi->isClosed()) {
            throw new ForbiddenException('Can\'t create a hearing for a closed Pi');
        }
        $hearingDateNoTime = new \DateTime($hearingDate->format('Y-m-d'));
        if ($hearingDateNoTime < $pi->getAgreedDate(true)) {
            throw new ValidationException(
                [self::MSG_HEARING_DATE_BEFORE_PI_DATE => $pi->getAgreedDate(true)->format('Y-m-d')]
            );
        }

        $this->pi = $pi;
        $this->presidingTc = $presidingTc;
        $this->presidedByRole = $presidedByRole;
        $this->venue = $venue;
        $this->venueOther = $venueOther;
        $this->hearingDate = $hearingDate;
        $this->witnesses = $witnesses;
        $this->drivers = $drivers;
        $this->details = $details;
        $this->isCancelled = $isCancelled;
        $this->isAdjourned = $isAdjourned;

        $this->processAdjournedAndCancelled(
            $isCancelled,
            $cancelledReason,
            $cancelledDate,
            $isAdjourned,
            $adjournedReason,
            $adjournedDate
        );
    }

    /**
     * @param VenueEntity|null $venue
     * @param string           $venueOther
     * @param int              $witnesses
     * @param int              $drivers
     * @param string           $isCancelled
     * @param string           $cancelledDate
     * @param string           $cancelledReason
     * @param string           $isAdjourned
     * @param string           $adjournedDate
     * @param string           $adjournedReason
     * @param string           $details
     * @throws ForbiddenException
     * @return void
     */
    public function update(
        PresidingTcEntity $presidingTc,
        RefData $presidedByRole,
        \DateTime $hearingDate,
        $venue,
        $venueOther,
        $witnesses,
        $drivers,
        $isCancelled,
        $cancelledDate,
        $cancelledReason,
        $isAdjourned,
        $adjournedDate,
        $adjournedReason,
        $details
    ) {
        if ($this->getPi()->isClosed()) {
            throw new ForbiddenException('Can\'t update a hearing for a closed Pi');
        }

        $hearingDateNoTime = new \DateTime($hearingDate->format('Y-m-d'));
        if ($hearingDateNoTime < $this->getPi()->getAgreedDate(true)) {
            throw new ValidationException(
                [self::MSG_HEARING_DATE_BEFORE_PI_DATE => $this->getPi()->getAgreedDate(true)->format('Y-m-d')]
            );
        }

        $this->presidingTc = $presidingTc;
        $this->presidedByRole = $presidedByRole;
        $this->venue = $venue;
        $this->venueOther = $venueOther;
        $this->hearingDate = $hearingDate;
        $this->witnesses = $witnesses;
        $this->drivers = $drivers;
        $this->details = $details;
        $this->isCancelled = $isCancelled;
        $this->isAdjourned = $isAdjourned;

        $this->processAdjournedAndCancelled(
            $isCancelled,
            $cancelledReason,
            $cancelledDate,
            $isAdjourned,
            $adjournedReason,
            $adjournedDate
        );
    }

    private function processAdjournedAndCancelled(
        $isCancelled,
        $cancelledReason,
        $cancelledDate,
        $isAdjourned,
        $adjournedReason,
        $adjournedDate
    ) {
        if ($isCancelled == 'Y') {
            $this->cancelledDate = $this->processDate($cancelledDate);
            $this->cancelledReason = $cancelledReason;
        } else {
            $this->cancelledReason = null;
            $this->cancelledDate = null;
        }

        if ($isAdjourned == 'Y') {
            $this->adjournedDate = $this->processDate($adjournedDate, \DateTime::ISO8601, false);
            $this->adjournedReason = $adjournedReason;
        } else {
            $this->adjournedReason = null;
            $this->adjournedDate = null;
        }
    }
}
