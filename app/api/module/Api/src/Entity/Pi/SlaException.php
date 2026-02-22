<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;

/**
 * SlaException
 *
 * @ORM\Entity
 * @ORM\Table(name="sla_exception")
 */
class SlaException extends AbstractSlaException
{
    /**
     * Constructor
     *
     * @param string    $slaDescription         SLA description
     * @param string    $slaExceptionDescription SLA exception description
     * @param \DateTime $effectiveFrom          Effective from date
     */
    public function __construct($slaDescription, $slaExceptionDescription, $effectiveFrom)
    {
        $this->setSlaDescription($slaDescription);
        $this->setSlaExceptionDescription($slaExceptionDescription);
        $this->setEffectiveFrom($effectiveFrom);
    }

    /**
     * Get identifier
     *
     * @return int
     */
    #[\Override]
    public function getId()
    {
        return $this->id;
    }

    /**
     * Check if SLA exception is currently active
     *
     * @param \DateTime|null $checkDate Date to check against (default: now)
     *
     * @return bool
     */
    public function isActive($checkDate = null)
    {
        if ($checkDate === null) {
            $checkDate = new \DateTime();
        }

        // Must be after effective from date
        if ($this->getEffectiveFrom(true) > $checkDate) {
            return false;
        }

        // If effective to is set, must be before that date
        if ($this->getEffectiveTo(true) !== null && $this->getEffectiveTo(true) < $checkDate) {
            return false;
        }

        return true;
    }

    /**
     * String representation
     *
     * @return string
     */
    #[\Override]
    public function __toString(): string
    {
        return $this->getSlaDescription() . ' - ' . $this->getSlaExceptionDescription();
    }
}
