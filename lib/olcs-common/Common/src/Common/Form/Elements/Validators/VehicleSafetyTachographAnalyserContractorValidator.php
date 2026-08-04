<?php

/**
 * VehicleSafetyTachographAnalyserContractorValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Common\Form\Elements\Validators;

use Laminas\Validator\AbstractValidator;

/**
 * VehicleSafetyTachographAnalyserContractorValidator
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleSafetyTachographAnalyserContractorValidator extends AbstractValidator
{
    /**
     * Message templates
     *
     * @var array
     */
    protected $messageTemplates = [
        'required' => "Value is required and can't be empty"
    ];

    /**
     * Custom validation for tachograph analyser
     *
     * @param mixed $value
     * @param array $context
     */
    #[\Override]
    public function isValid($value, $context = null)
    {
        unset($value);

        if (
            $context['tachographIns'] === 'tach_external'
            && trim($context['tachographInsName']) === ''
        ) {
            $this->error('required');

            return false;
        }

        return true;
    }
}
