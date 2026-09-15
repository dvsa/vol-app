<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait GovUkAccountJourney
 *
 * @package Dvsa\Olcs\Transfer\FieldType\Traits
 */
trait GovUkAccountJourney
{
    /**
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={"haystack": {"jrny_new_application","jrny_continuation","jrny_variation","jrny_tm_application","jrny_surrender"}})
     */
    protected $journey = null;

    public function getJourney()
    {
        return $this->journey;
    }

    /**
     * setJourney
     */
    public function setJourney(string $journey)
    {
        $this->journey = $journey;
    }
}
