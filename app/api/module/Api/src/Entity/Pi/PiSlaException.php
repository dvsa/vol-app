<?php

namespace Dvsa\Olcs\Api\Entity\Pi;

use Doctrine\ORM\Mapping as ORM;

/**
 * PiSlaException
 *
 * @ORM\Entity
 * @ORM\Table(name="pi_sla_exception")
 */
class PiSlaException extends AbstractPiSlaException
{
    /**
     * Constructor
     *
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi           $pi           PI entity
     * @param \Dvsa\Olcs\Api\Entity\Pi\SlaException $slaException SLA Exception entity
     */
    public function __construct($pi, $slaException)
    {
        $this->setPi($pi);
        $this->setSlaException($slaException);
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
     * Get the case from the PI
     *
     * @return \Dvsa\Olcs\Api\Entity\Cases\Cases|null
     */
    public function getCase()
    {
        return $this->getPi() ? $this->getPi()->getCase() : null;
    }

    /**
     * Check if the linked SLA exception is currently active
     *
     * @param \DateTime|null $checkDate Date to check against (default: now)
     *
     * @return bool
     */
    public function isSlaExceptionActive($checkDate = null)
    {
        return $this->getSlaException() ? $this->getSlaException()->isActive($checkDate) : false;
    }

    /**
     * String representation
     *
     * @return string
     */
    #[\Override]
    public function __toString(): string
    {
        $piId = $this->getPi() ? $this->getPi()->getId() : 'Unknown';
        $exceptionDesc = $this->getSlaException() ? $this->getSlaException()->getSlaDescription() : 'Unknown';

        return "PI {$piId} - {$exceptionDesc}";
    }
}
