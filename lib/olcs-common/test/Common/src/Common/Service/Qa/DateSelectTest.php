<?php

namespace CommonTest\Service\Qa;

use Common\Service\Qa\DateNotInPastValidator;
use Common\Service\Qa\DateSelect;
use Common\Service\Qa\DateValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DateSelectTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateSelectTest extends MockeryTestCase
{
    private $dateSelect;

    #[\Override]
    protected function setUp(): void
    {
        $this->dateSelect = m::mock(DateSelect::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testSetValueWithString(): void
    {
        $value = '2020-05-22';

        $expectedArray = [
            'year' => '2020',
            'month' => '05',
            'day' => '22'
        ];

        $this->dateSelect->shouldReceive('callParentSetValue')
            ->with($expectedArray)
            ->once();

        $this->dateSelect->setValue($value);
    }

    /**
     * @dataProvider dpSetValueWithOther
     */
    public function testSetValueWithOther($value): void
    {
        $this->dateSelect->shouldReceive('callParentSetValue')
            ->with($value)
            ->once();

        $this->dateSelect->setValue($value);
    }

    /**
     * @return (int|string[]|true)[][]
     *
     * @psalm-return list{list{array{key1: 'value1', key2: 'value2'}}, list{431}, list{true}}
     */
    public function dpSetValueWithOther(): array
    {
        return [
            [
                ['key1' => 'value1', 'key2' => 'value2']
            ],
            [[431]],
            [[true]],
        ];
    }

    public function testGetInputSpecification(): void
    {
        $name = 'foo';
        $invalidDateKey = 'qanda.date.error.invalid-date';
        $dateInPastKey = 'qanda.date.error.date-in-past';

        $options = [
            'invalidDateKey' => $invalidDateKey,
            'dateInPastKey' => $dateInPastKey
        ];

        $this->dateSelect->setOptions($options);

        $expectedSpecification = [
            'id' => 'qaDateSelect',
            'name' => $name,
            'required' => false,
            'filters' => [
                [
                    'name' => 'DateSelect'
                ]
            ],
            'validators' => [
                [
                    'name' => DateValidator::class,
                    'options' => [
                        'format' => 'Y-m-d',
                        'break_chain_on_failure' => true,
                        'messages' => [
                            DateValidator::INVALID_DATE => $invalidDateKey
                        ]
                    ]
                ],
                [
                    'name' => DateNotInPastValidator::class,
                    'options' => [
                        'messages' => [
                            DateNotInPastValidator::ERR_DATE_IN_PAST => $dateInPastKey
                        ]
                    ]
                ]
            ]
        ];

        $this->dateSelect->setName($name);

        $this->assertEquals(
            $expectedSpecification,
            $this->dateSelect->getInputSpecification()
        );
    }
}
