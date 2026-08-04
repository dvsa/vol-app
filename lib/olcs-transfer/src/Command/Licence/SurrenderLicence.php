<?php

/**
 * SurrenderLicence.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Command\Licence;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Command\AbstractCommand;
use Dvsa\Olcs\Transfer\FieldType;

/**
 * @Transfer\RouteName("backend/licence/single/decisions/surrender")
 * @Transfer\Method("POST")
 */
final class SurrenderLicence extends AbstractCommand
{
    use FieldType\Traits\DecisionsOptional;

    /**
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $id;

    /**
     * @var \DateTime
     * @Transfer\Validator("Laminas\Validator\Date", options={"format": "Y-m-d"})
     */
    protected $surrenderDate;

    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $terminated = false;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getSurrenderDate()
    {
        return $this->surrenderDate;
    }

    /**
     * @return boolean
     */
    public function isTerminated()
    {
        return $this->terminated;
    }
}
