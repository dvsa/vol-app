<?php

namespace Dvsa\Olcs\Api\Domain\Query\IrhpPermitRange;

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
    protected $permitStock;

    /**
     * @var int
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $permitNumber;

    /**
     * Gets permit stock
     *
     * @return int
     */
    public function getPermitStock()
    {
        return $this->permitStock;
    }

    /**
     * Gets permit number
     *
     * @return int
     */
    public function getPermitNumber()
    {
        return $this->permitNumber;
    }
}
