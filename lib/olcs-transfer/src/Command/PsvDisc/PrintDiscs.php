<?php

/**
 * Print PSV discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\PsvDisc;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/psv-disc/print-discs")
 * @Transfer\Method("POST")
 */
final class PrintDiscs extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"ltyp_r","ltyp_sn","ltyp_si","ltyp_sr"}})
     * @Transfer\Optional
     */
    public $licenceType;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $startNumber;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    public $maxPages;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    public $discSequence;

    public function getLicenceType()
    {
        return $this->licenceType;
    }

    public function getStartNumber()
    {
        return $this->startNumber;
    }

    public function getDiscSequence()
    {
        return $this->discSequence;
    }

    /**
     * Get the value of maxPages
     *
     * @return mixed
     */
    public function getMaxPages()
    {
        return $this->maxPages;
    }
}
