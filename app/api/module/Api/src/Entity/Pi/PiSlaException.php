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
     * @param \Dvsa\Olcs\Api\Entity\Pi\Pi           $pi          PI entity
     * @param \Dvsa\Olcs\Api\Entity\Pi\SlaException $piException SLA Exception entity
     */
    public function __construct($pi, $piException)
    {
        $this->setPi($pi);
        $this->setPiException($piException);
    }

    /**
     * Get identifier
     *
     * @return int
     */
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
        return $this->getPiException() ? $this->getPiException()->isActive($checkDate) : false;
    }

    /**
     * String representation
     *
     * @return string
     */
    public function __toString()
    {
        $piId = $this->getPi() ? $this->getPi()->getId() : 'Unknown';
        $exceptionDesc = $this->getPiException() ? $this->getPiException()->getSlaDescription() : 'Unknown';
        
        return "PI {$piId} - {$exceptionDesc}";
    }
}
