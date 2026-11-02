<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait DecisionsOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
trait DecisionsOptional
{
    #[Transfer\Optional]
    #[Transfer\ArrayInput]
    #[Transfer\ArrayFilter('Dvsa\Olcs\Transfer\Filter\FilterEmptyItems')]
    #[Transfer\ArrayFilter('Dvsa\Olcs\Transfer\Filter\UniqueItems')]
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $decisions = [];

    /**
     * @return array
     */
    public function getDecisions()
    {
        return $this->decisions;
    }
}
