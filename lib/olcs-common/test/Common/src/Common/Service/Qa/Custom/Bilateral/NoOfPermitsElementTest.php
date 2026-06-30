<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\NoOfPermitsElement;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Filter\StringTrim;
use Laminas\Validator\Digits;
use Laminas\Validator\GreaterThan;
use Laminas\Validator\StringLength;

/**
 * NoOfPermitsElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsElementTest extends MockeryTestCase
{
    public const ELEMENT_NAME = 'elementName';

    private $noOfPermitsElement;

    #[\Override]
    protected function setUp(): void
    {
        $this->noOfPermitsElement = new NoOfPermitsElement(self::ELEMENT_NAME);
    }

    public function testGetInputSpecification(): void
    {
        $expectedInputSpecification = [
            'name' => self::ELEMENT_NAME,
            'required' => false,
            'continue_if_empty' => true,
            'filters' => [
                [
                    'name' => StringTrim::class
                ]
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'min' => 1,
                        'max' => NoOfPermitsElement::MAX_LENGTH,
                        'break_chain_on_failure' => true,
                        'messages' => [
                            StringLength::TOO_SHORT => NoOfPermitsElement::ERROR_KEY,
                            StringLength::TOO_LONG => NoOfPermitsElement::ERROR_KEY
                        ]
                    ]
                ],
                [
                    'name' => Digits::class,
                    'options' => [
                        'break_chain_on_failure' => true,
                        'messages' => [
                            Digits::NOT_DIGITS => NoOfPermitsElement::ERROR_KEY
                        ]
                    ]
                ],
                [
                    'name' => GreaterThan::class,
                    'options' => [
                        'min' => 0,
                        'inclusive' => false,
                        'messages' => [
                            GreaterThan::NOT_GREATER => NoOfPermitsElement::ERROR_KEY
                        ]
                    ]
                ]
            ],
        ];

        $this->assertEquals(
            $expectedInputSpecification,
            $this->noOfPermitsElement->getInputSpecification()
        );
    }

    public function testGetMaxLengthAttribute(): void
    {
        $this->assertEquals(
            NoOfPermitsElement::MAX_LENGTH,
            $this->noOfPermitsElement->getAttribute('maxLength')
        );
    }
}
