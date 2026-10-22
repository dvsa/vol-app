<?php

namespace Dvsa\Olcs\Api\Domain\Command\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * End IRHP Applications and Permits Context Trait
 *
 * @package Dvsa\Olcs\Api\Domain\Command\Traits
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait EndIrhpApplicationsAndPermitsContext
{
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\StringLength', options: ['min' => 1])]
    #[Transfer\Validator('Laminas\Validator\InArray', options: ['haystack' => ['context_surrender', 'context_revoke', 'context_cns']])]
    protected $context;

    /**
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }
}
