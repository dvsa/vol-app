<?php

namespace Dvsa\Olcs\Api\Domain\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class ByPermitNumber
 */
class ByPermitNumber extends AbstractQuery
{
    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $permitNumber;

    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $irhpPermitRange;

    /**
     * Gets permit number
     *
     * @return int
     */
    public function getPermitNumber()
    {
        return $this->permitNumber;
    }

    /**
     * @return int
     */
    public function getIrhpPermitRange()
    {
        return $this->irhpPermitRange;
    }
}
