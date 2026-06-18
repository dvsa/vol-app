<?php

/**
 * Create Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\OtherLicence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/other-licence")
 * @Transfer\Method("POST")
 */
final class CreateOtherLicence extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $licNo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $application;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $holderName;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     * @Transfer\Optional
     */
    public $willSurrender;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    public $previousLicenceType;

    /**
     * @Transfer\Validator("Date",options={"format":"Y-m-d"})
     * @Transfer\Optional
     */
    public $disqualificationDate;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    public $disqualificationLength;

    /**
     * @Transfer\Validator("Date",options={"format":"Y-m-d"})
     * @Transfer\Optional
     */
    public $purchaseDate;

    public function getApplication()
    {
        return $this->application;
    }

    public function getLicNo()
    {
        return $this->licNo;
    }

    public function getHolderName()
    {
        return $this->holderName;
    }

    public function getWillSurrender()
    {
        return $this->willSurrender;
    }

    public function getPreviousLicenceType()
    {
        return $this->previousLicenceType;
    }

    public function getDisqualificationDate()
    {
        return $this->disqualificationDate;
    }

    public function getDisqualificationLength()
    {
        return $this->disqualificationLength;
    }

    public function getPurchaseDate()
    {
        return $this->purchaseDate;
    }
}
