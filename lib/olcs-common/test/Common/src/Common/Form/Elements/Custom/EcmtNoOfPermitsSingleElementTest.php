<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\EcmtNoOfPermitsElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsSingleElement;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsSingleValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Laminas\Validator\GreaterThan;

/**
 * EcmtNoOfPermitsSingleElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtNoOfPermitsSingleElementTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGetInputSpecification
     */
    public function testGetInputSpecification($emissionsCategory, $skipAvailabilityValidation, $expectAvailabilityValidator): void
    {
        $maxPermitted = 77;
        $permitsRemaining = 22;

        $ecmtNoOfPermitsSingleElement = m::mock(EcmtNoOfPermitsSingleElement::class)->makePartial()
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

        $ecmtNoOfPermitsSingleElement->shouldReceive('callParentGetInputSpecification')
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
                            GreaterThan::NOT_GREATER => EcmtNoOfPermitsSingleElement::GENERIC_ERROR_KEY
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
                'name' => NoOfPermitsSingleValidator::class,
                'options' => [
                    'maxPermitted' => $maxPermitted,
                    'permitsRemaining' => $permitsRemaining,
                    'emissionsCategory' => $emissionsCategory,
                ]
            ];
        }

        $ecmtNoOfPermitsSingleElement->setOption('maxPermitted', $maxPermitted);
        $ecmtNoOfPermitsSingleElement->setOption('permitsRemaining', $permitsRemaining);
        $ecmtNoOfPermitsSingleElement->setOption('emissionsCategory', $emissionsCategory);
        $ecmtNoOfPermitsSingleElement->setOption('skipAvailabilityValidation', $skipAvailabilityValidation);

        $this->assertInstanceOf(EcmtNoOfPermitsElement::class, $ecmtNoOfPermitsSingleElement);

        $this->assertEquals(
            $expectedInputSpecification,
            $ecmtNoOfPermitsSingleElement->getInputSpecification()
        );
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return list{list{'euro5', false, true}, list{'euro5', true, false}, list{'euro6', false, true}, list{'euro6', true, false}}
     */
    public function dpGetInputSpecification(): array
    {
        return [
            ['euro5', false, true],
            ['euro5', true, false],
            ['euro6', false, true],
            ['euro6', true, false],
        ];
    }
}
