<?php

/**
 * Replace IRHP Permit
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Command\IrhpPermit;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/irhp-permits/single/replace")
 * @Transfer\Method("POST")
 */
final class Replace extends AbstractCommand
{
    use Identity;

    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $replacementIrhpPermit;

    /**
     * @return int
     */
    public function getReplacementIrhpPermit(): int
    {
        return $this->replacementIrhpPermit;
    }
}
