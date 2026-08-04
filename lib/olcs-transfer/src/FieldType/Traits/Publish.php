<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Publish
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait Publish
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $publish;

    /**
     * @return string
     */
    public function getPublish()
    {
        return $this->publish;
    }
}
