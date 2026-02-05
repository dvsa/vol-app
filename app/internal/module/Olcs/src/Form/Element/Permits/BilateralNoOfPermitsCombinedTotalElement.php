<?php

namespace Olcs\Form\Element\Permits;

use Laminas\Form\Element\Hidden;
use Laminas\InputFilter\InputProviderInterface;
use Laminas\Validator\Callback;

class BilateralNoOfPermitsCombinedTotalElement extends Hidden implements InputProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'continue_if_empty' => true,
            'validators' => [
                [
                    'name' => Callback::class,
                    'options' => [
                        'callback' => BilateralNoOfPermitsCombinedTotalValidator::validateNonZeroValuePresent(...),
                        'messages' => [
                            Callback::INVALID_VALUE => 'Enter a number of permits in at least one field'
                        ]
                    ],
                ],
            ],
        ];
    }
}
