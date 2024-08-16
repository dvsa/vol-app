<?php

/**
 * Create Replacement IRHP Permit
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Api\Domain\Command\IrhpPermit;

use Dvsa\Olcs\Transfer\Command\AbstractCommand;

final class ReplacementIrhpPermit extends AbstractCommand
{
    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $replaces;

    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irhpPermitRange;

    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $permitNumber;

    /**
     * @return int
     */
    public function getReplaces()
    {
        return $this->replaces;
    }

    /**
     * @return int
     */
    public function getIrhpPermitRange()
    {
        return $this->irhpPermitRange;
    }

    /**
     * @return int
     */
    public function getPermitNumber()
    {
        return $this->permitNumber;
    }
}
