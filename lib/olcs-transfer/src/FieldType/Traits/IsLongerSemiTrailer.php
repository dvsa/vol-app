<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Is Longer Semi Trailer
 *
 * @package Dvsa\Olcs\Transfer\Command\FieldType\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait IsLongerSemiTrailer
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"Y", "N"}})
     */
    protected $isLongerSemiTrailer;

    /**
     * @return string
     */
    public function getIsLongerSemiTrailer()
    {
        return $this->isLongerSemiTrailer;
    }
}
