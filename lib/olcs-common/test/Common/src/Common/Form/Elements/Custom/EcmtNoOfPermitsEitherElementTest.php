<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\EcmtNoOfPermitsEitherElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsElement;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsEitherValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Validator\GreaterThan;

/**
 * EcmtNoOfPermitsEitherElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtNoOfPermitsEitherElementTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGetInputSpecification
     */
    public function testGetInputSpecification($skipAvailabilityValidation, $expectAvailabilityValidator): void
    {
        $maxPermitted = 60;
        $emissionsCategoryPermitsRemaining = [
            'euro5' => 16,
            'euro6' => 10
        ];

        $ecmtNoOfPermitsEitherElement = m::mock(EcmtNoOfPermitsEitherElement::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $parentInputSpecification = [
            'key1' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ],
            'validators' => [
                [
                    'validator1key1' => 'validator1value1',
                    'validator1key2' => 'validator1value2',
                ],
                [
                    'validator2key1' => 'validator2value1',
                    'validator2key2' => 'validator2value2',
                ]
            ],
            'key3' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ]
        ];

        $ecmtNoOfPermitsEitherElement->shouldReceive('callParentGetInputSpecification')
            ->withNoArgs()
            ->andReturn($parentInputSpecification);

        $expectedInputSpecification = [
            'key1' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ],
            'validators' => [
                [
                    'validator1key1' => 'validator1value1',
                    'validator1key2' => 'validator1value2',
                ],
                [
                    'validator2key1' => 'validator2value1',
                    'validator2key2' => 'validator2value2',
                ],
                [
                    'name' => GreaterThan::class,
                    'options' => [
                        'min' => 0,
                        'messages' => [
                            GreaterThan::NOT_GREATER => EcmtNoOfPermitsEitherElement::GENERIC_ERROR_KEY
                        ]
                    ]
                ],
            ],
            'key3' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ]
        ];

        if ($expectAvailabilityValidator) {
            $expectedInputSpecification['validators'][] = [
                'name' => NoOfPermitsEitherValidator::class,
                'options' => [
                    'maxPermitted' => $maxPermitted,
                    'emissionsCategoryPermitsRemaining' => $emissionsCategoryPermitsRemaining
                ]
            ];
        }

        $ecmtNoOfPermitsEitherElement->setOption('maxPermitted', $maxPermitted);
        $ecmtNoOfPermitsEitherElement->setOption(
            'emissionsCategoryPermitsRemaining',
            $emissionsCategoryPermitsRemaining
        );
        $ecmtNoOfPermitsEitherElement->setOption('skipAvailabilityValidation', $skipAvailabilityValidation);

        $this->assertInstanceOf(EcmtNoOfPermitsElement::class, $ecmtNoOfPermitsEitherElement);

        $this->assertEquals(
            $expectedInputSpecification,
            $ecmtNoOfPermitsEitherElement->getInputSpecification()
        );
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{false, true}, list{true, false}}
     */
    public function dpGetInputSpecification(): array
    {
        return [
            [false, true],
            [true, false],
        ];
    }
}
