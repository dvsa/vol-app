<?php

namespace Dvsa\Olcs\Api\Entity\Organisation;

use Doctrine\ORM\Mapping as ORM;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Disqualification Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="disqualification",
 *    indexes={
 *        @ORM\Index(name="ix_disqualification_organisation_id", columns={"organisation_id"}),
 *        @ORM\Index(name="ix_disqualification_created_by", columns={"created_by"}),
 *        @ORM\Index(name="ix_disqualification_last_modified_by", columns={"last_modified_by"}),
 *        @ORM\Index(name="ix_disqualification_person_idx", columns={"person_id"})
 *    },
 *    uniqueConstraints={
 *        @ORM\UniqueConstraint(name="uk_disqualification_olbs_key", columns={"olbs_key"})
 *    }
 * )
 */
class Disqualification extends AbstractDisqualification
{
    public const STATUS_NONE = 'None';
    public const STATUS_ACTIVE = 'Active';
    public const STATUS_INACTIVE = 'Inactive';

    /**
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     */
    public function __construct(
        Organisation $organisation = null,
        \Dvsa\Olcs\Api\Entity\Person\Person $person = null
    ) {

        if ($organisation === null && $person === null) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['DISQ_MISSING_ORG_OFFICER' => 'Organisation or Person must be specified']
            );
        }

        if ($organisation !== null && $person !== null) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['DISQ_BOTH_ORG_OFFICER' => 'You cannot specify both Organisation and Person']
            );
        }

        $this->setOrganisation($organisation);
        $this->setPerson($person);
    }

    /**
     * Update and validate entity
     *
     * @param string    $isDisqualified Y or N
     * @param \DateTime $startDate
     * @param string    $notes
     * @param int       $period
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\ValidationException
     */
    public function update(
        $isDisqualified,
        \DateTime $startDate = null,
        $notes = null,
        $period = null
    ) {

        if ($isDisqualified == 'Y' && empty($startDate)) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\ValidationException(
                ['DISQ_START_DATE_MISSING' => 'Start date must be specified if isDisqualified']
            );
        }

        $this->setIsDisqualified($isDisqualified)
            ->setStartDate($startDate)
            ->setNotes($notes)
            ->setPeriod($period);
    }

    /**
     * Get the current status of this Disqualification
     *
     * @return string self::STATUS_ACTIVE or self::STATUS_INACTIVE
     */
    public function getStatus()
    {
        // if there is a disqualification record where is_disqualified = 1 and the current date falls between the
        // start date and end date (end_date = start_date + period (months)). The disqualification is also active
        // if is_disqualified = 1 and the start_date is today or in the past and period is NULL or 0
        if ($this->getIsDisqualified() === 'Y') {
            $startDate = new DateTime($this->getStartDate());
            $endDate = $this->getEndDate();
            if ($endDate === null) {
                // if endDate is null, make sure its in the future (indefinate), set to today + 1 month
                $endDate = new DateTime('+1 month');
            }

            $now = new DateTime('now');
            if (
                $now->getTimestamp() >= $startDate->getTimestamp() &&
                $now->getTimestamp() <= $endDate->getTimestamp()
            ) {
                return self::STATUS_ACTIVE;
            }
        }
        return self::STATUS_INACTIVE;
    }

    /**
     * Get (calculate) the end date for the disqualification
     *
     * @return null|\DateTime
     */
    public function getEndDate()
    {
        if (empty($this->getPeriod())) {
            // if period is empty then its indefinate
            $endDate = null;
        } else {
            $startDate = $this->getStartDate();
            if (!($startDate instanceof \DateTime)) {
                $startDate = new \DateTime($startDate);
            }

            $endDate = $startDate->add(new \DateInterval('P' . (int) $this->getPeriod() . 'M'));
        }

        return $endDate;
    }

    /**
     * Serialize properties
     *
     * @return array
     */
    public function getCalculatedBundleValues()
    {
        $endDate = $this->getEndDate();
        return [
            'endDate' => ($endDate instanceof \DateTime) ? $endDate->format(\DateTime::W3C) : null,
            'status' => $this->getStatus(),
        ];
    }
}
