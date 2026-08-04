<?php

/**
 * Update Other Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\OtherLicence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/other-licence/single")
 * @Transfer\Method("PUT")
 */
final class UpdateOtherLicence extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    public $licNo;

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

    public function getId()
    {
        return $this->id;
    }

    public function getVersion()
    {
        return $this->version;
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
