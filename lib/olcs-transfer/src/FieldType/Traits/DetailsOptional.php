<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Optional Details
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait DetailsOptional
{
    /**
     * @var String
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\StringLength",options={"min":5,"max":4000})
     */
    protected $details;

    /**
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }
}
