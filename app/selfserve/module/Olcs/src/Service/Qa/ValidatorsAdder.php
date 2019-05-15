<?php

namespace Olcs\Service\Qa;

use Zend\Form\Element;

class ValidatorsAdder
{
    /**
     * Add validators to the specified form using the supplied array representation
     *
     * @param mixed $form
     * @param array $validators
     */
    public function add($form, array $validators)
    {
        $validatorChain = $form->getInputFilter()->get('fields')->get('qaElement')->getValidatorChain();

        foreach ($validators as $validator) {
            $validatorChain->attachByName(
                $validator['rule'],
                $validator['parameters']
            );
        }
    }
}
