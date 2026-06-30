<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\EcmtNoOfPermitsElement;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Digits;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;

/**
 * EcmtNoOfPermitsElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtNoOfPermitsElementTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        $name = 'permitsRequired';

        $expectedInputSpecification = [
            'name' => $name,
            'required' => false,
            'continue_if_empty' => true,
            'filters' => [
                [
                    'name' => StringTrim::class
                ]
            ],
            'validators' => [
                [
                    'name' => NotEmpty::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            NotEmpty::IS_EMPTY => EcmtNoOfPermitsElement::GENERIC_ERROR_KEY
                        ]
                    ]
                ],
                [
                    'name' => StringLength::class,
                    'options' => [
                        'max' => EcmtNoOfPermitsElement::MAX_LENGTH,
                        'break_chain_on_failure' => true,
                        'messages' => [
                            StringLength::INVALID => EcmtNoOfPermitsElement::GENERIC_ERROR_KEY,
                            StringLength::TOO_LONG => EcmtNoOfPermitsElement::GENERIC_ERROR_KEY
                        ]
                    ]
                ],
                [
                    'name' => Digits::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            Digits::NOT_DIGITS => EcmtNoOfPermitsElement::GENERIC_ERROR_KEY
                        ]
                    ]
                ]
            ]
        ];

        $ecmtNoOfPermitsElement = new EcmtNoOfPermitsElement($name);

        $this->assertEquals(
            $expectedInputSpecification,
            $ecmtNoOfPermitsElement->getInputSpecification()
        );
    }
}
