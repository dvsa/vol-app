<?php

namespace CommonTest\Service\Qa\Custom\Common;

use Common\Service\Qa\Custom\Common\DateBeforeValidator;
use Common\Service\Qa\Custom\Common\DateSelectMustBeBefore;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DateSelectMustBeBeforeTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateSelectMustBeBeforeTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        $dateMustBeBefore = '2020-05-02';
        $dateNotBeforeKey = 'date.not.before.key';

        $dateSelectMustBeBefore = m::mock(DateSelectMustBeBefore::class)->makePartial()
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

        $dateSelectMustBeBefore->shouldReceive('callParentGetInputSpecification')
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
                    'name' => DateBeforeValidator::class,
                    'options' => [
                        'dateMustBeBefore' => $dateMustBeBefore,
                        'messages' => [
                            DateBeforeValidator::ERR_DATE_NOT_BEFORE => $dateNotBeforeKey
                        ]
                    ]
                ]
            ],
            'key3' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ]
        ];

        $options = [
            'dateMustBeBefore' => $dateMustBeBefore,
            'dateNotBeforeKey' => $dateNotBeforeKey
        ];

        $dateSelectMustBeBefore->setOptions($options);

        $this->assertEquals(
            $expectedInputSpecification,
            $dateSelectMustBeBefore->getInputSpecification()
        );
    }
}
