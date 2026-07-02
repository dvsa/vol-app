<?php

namespace Common\Form\Elements\InputFilters;

use Common\Form\Elements\Validators\VehicleSafetyTachographAnalyserContractorValidator;

/**
 * @deprecated This does not get used and must be removed as in: OLCS-15198
 *
 * VehicleSafetyTachographAnalyserContractor
 */
class VehicleSafetyTachographAnalyserContractor extends Text
{
    protected $continueIfEmpty = true;

    protected $isAllowEmpty = false;

    /**
     * Get a list of validators
     *
     * @return array
     */
    #[\Override]
    protected function getValidators()
    {
        $validators = parent::getValidators();

        return array_merge(
            $validators,
            [new VehicleSafetyTachographAnalyserContractorValidator()]
        );
    }
}
