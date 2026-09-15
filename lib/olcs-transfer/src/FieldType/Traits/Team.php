<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Team
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
trait Team
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $team;

    /**
     * @return int
     */
    public function getTeam()
    {
        return $this->team;
    }
}
