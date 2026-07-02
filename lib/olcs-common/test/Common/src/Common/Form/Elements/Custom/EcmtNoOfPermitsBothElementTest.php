<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Filter\NotPopulatedStringToZero;
use Common\Form\Elements\Custom\EcmtNoOfPermitsBothElement;
use Common\Form\Elements\Custom\EcmtNoOfPermitsElement;
use Common\Service\Qa\Custom\Ecmt\NoOfPermitsBothValidator;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * EcmtNoOfPermitsBothElementTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EcmtNoOfPermitsBothElementTest extends MockeryTestCase
{
    /**
     * @dataProvider dpGetInputSpecification
     */
    public function testGetInputSpecification($emissionsCategory, $skipAvailabilityValidation, $expectAvailabilityValidator): void
    {
        $permitsRemaining = 55;

        $ecmtNoOfPermitsBothElement = m::mock(EcmtNoOfPermitsBothElement::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $parentInputSpecification = [
            'key1' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ],
            'filters' => [
                [
                    'filter1key1' => 'filter1value1',
                    'filter1key2' => 'filter1value2',
                ],
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

        $ecmtNoOfPermitsBothElement->shouldReceive('callParentGetInputSpecification')
            ->withNoArgs()
            ->andReturn($parentInputSpecification);

        $expectedInputSpecification = [
            'key1' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ],
            'filters' => [
                [
                    'filter1key1' => 'filter1value1',
                    'filter1key2' => 'filter1value2',
                ],
                [
                    'name' => NotPopulatedStringToZero::class
                ]
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
            ],
            'key3' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ]
        ];

        if ($expectAvailabilityValidator) {
            $expectedInputSpecification['validators'][] = [
                'name' => NoOfPermitsBothValidator::class,
                'options' => [
                    'permitsRemaining' => $permitsRemaining,
                    'emissionsCategory' => $emissionsCategory
                ]
            ];
        }

        $ecmtNoOfPermitsBothElement->setOption('permitsRemaining', $permitsRemaining);
        $ecmtNoOfPermitsBothElement->setOption('emissionsCategory', $emissionsCategory);
        $ecmtNoOfPermitsBothElement->setOption('skipAvailabilityValidation', $skipAvailabilityValidation);

        $this->assertInstanceOf(EcmtNoOfPermitsElement::class, $ecmtNoOfPermitsBothElement);

        $this->assertEquals(
            $expectedInputSpecification,
            $ecmtNoOfPermitsBothElement->getInputSpecification()
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
