<?php

namespace Dvsa\Olcs\Transfer\Command\Partial;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * OperatingCentreEvidence partial
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatingCentreEvidence extends AbstractCommand
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $aocId;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $adPlacedIn;

    /**
     * @Transfer\Validator("Date", options={"format": "Y-m-d"})
     */
    protected $adPlacedDate;

    /**
     * Get application operating centre id
     *
     * @return int
     */
    public function getAocId()
    {
        return $this->aocId;
    }

    /**
     * Get adPlacedIn
     *
     * @return string
     */
    public function getAdPlacedIn()
    {
        return $this->adPlacedIn;
    }

    /**
     * Get adPlacedDate
     *
     * @return string
     */
    public function getAdPlacedDate()
    {
        return $this->adPlacedDate;
    }
}
