<?php

namespace Common\Service\Qa;

use Dvsa\Olcs\Transfer\Filter\Vrm as VrmFilter;

class ValidatorsAdder
{
    /**
     * Add validators for a single fieldset to the specified form using the supplied array representation
     */
    public function add(mixed $form, array $options): void
    {
        $validators = $options['validators'];

        if (count($validators) > 0) {
            $fieldsetName = $options['fieldsetName'];

            $input = $form->getInputFilter()->get('qa')->get($fieldsetName)->get('qaElement');
            $input->setContinueIfEmpty(true);
            $validatorChain = $input->getValidatorChain();

            foreach ($validators as $validator) {
                if (ltrim($validator['rule'], '\\') == \Dvsa\Olcs\Transfer\Validators\Vrm::class) {
                    $filterChain = $input->getFilterChain();
                    $filterChain->attachByName(VrmFilter::class);
                }

                $validatorChain->attachByName(
                    $validator['rule'],
                    $validator['params']
                );
            }
        }
    }
}
