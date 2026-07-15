<?php

/**
 * Create a Previous Licence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\OtherLicence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/other-licence/previous-licence")
 * @Transfer\Method("POST")
 */
final class CreatePreviousLicence extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $tmaId;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":18})
     */
    protected $licNo;

    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":0,"max":90})
     */
    protected $holderName;

    /**
     * Get the TMA ID
     *
     * @return int
     */
    public function getTmaId()
    {
        return $this->tmaId;
    }

    /**
     * Get the licence number (This is not a reference to a Licence entity)
     *
     * @return string
     */
    public function getLicNo()
    {
        return $this->licNo;
    }

    /**
     * Get teh holder name
     *
     * @return string
     */
    public function getHolderName()
    {
        return $this->holderName;
    }
}
