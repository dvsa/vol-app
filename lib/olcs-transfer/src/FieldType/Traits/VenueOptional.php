<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Venue
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait VenueOptional
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $venue;

    /**
     * @return int
     */
    public function getVenue()
    {
        return $this->venue;
    }
}
