<?php

/**
 * Create Other Licence for a Transport Manager
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\OtherLicence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;

/**
 * @Transfer\RouteName("backend/other-licence/transport-manager")
 * @Transfer\Method("POST")
 */
final class CreateForTm extends AbstractCommand
{
    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $transportManagerId;

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

    public function getTransportManagerId()
    {
        return $this->transportManagerId;
    }

    public function getLicNo()
    {
        return $this->licNo;
    }

    public function getHolderName()
    {
        return $this->holderName;
    }
}
