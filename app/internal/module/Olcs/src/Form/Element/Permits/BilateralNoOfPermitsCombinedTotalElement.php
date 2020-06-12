<?php

namespace Olcs\Form\Element\Permits;

use Zend\Form\Element\Hidden;
use Zend\InputFilter\InputProviderInterface;
use Zend\Validator\Callback;

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
                        'callback' => [
                            BilateralNoOfPermitsCombinedTotalValidator::class,
                            'validateNonZeroValuePresent'
                        ],
                        'messages' => [
                            Callback::INVALID_VALUE => 'Enter a number of permits in at least one field'
                        ]
                    ],
                ],
            ],
        ];
    }
}
