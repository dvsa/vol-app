<?php

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sla Entity
 *
 * @ORM\Entity
 * @ORM\Table(name="sla")
 */
class Sla extends AbstractSla
{
    public const WRITTEN_OUTCOME_DECISION = 'piwo_decision';
    public const WRITTEN_OUTCOME_REASON = 'piwo_reason';
    public const VERBAL_DECISION_ONLY = 'piwo_verbal';

    public function appliesTo(\DateTime $date)
    {
        if ($this->effectiveFrom !== null && $this->effectiveFrom > $date) {
            return false;
        }

        if ($this->effectiveTo !== null && $this->effectiveTo < $date) {
            return false;
        }

        return true;
    }
}
