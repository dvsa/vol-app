<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Command\Cases\Pi;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * Create PI SLA Exception Command
 *
 * @Transfer\RouteName("backend/pi/sla-exceptions")
 * @Transfer\Method("POST")
 */
class CreatePiSlaException extends AbstractCommand
{
    /**
     * Case ID
     *
     * @var ?int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected ?int $case = null;

    /**
     * SLA Exception ID
     *
     * @var ?int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected ?int $slaException = null;

    /**
     * Get Case ID
     *
     * @return ?int
     */
    public function getCase(): ?int
    {
        return $this->case;
    }

    /**
     * Get SLA Exception ID
     *
     * @return ?int
     */
    public function getSlaException(): ?int
    {
        return $this->slaException;
    }
}
