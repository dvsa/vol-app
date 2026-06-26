<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\VehiclePlatedWeight;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Form\Elements\Custom\VehiclePlatedWeight
 */
class VehiclePlatedWeightTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        /** @var VehiclePlatedWeight $sut */
        $sut = m::mock(VehiclePlatedWeight::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        static::assertEquals('unit_Name', $actual['name']);
        static::assertEquals(
            [
                \Laminas\Validator\Digits::class,
                \Laminas\Validator\Between::class,
            ],
            array_map(
                static fn($item) => $item['name'],
                $actual['validators']
            )
        );
    }

    /**
     * @dataProvider dpTestGetInputSpecificationOptions
     */
    public function testGetInputSpecificationOptions($options, $expect): void
    {
        /** @var VehiclePlatedWeight $sut */
        $sut = new VehiclePlatedWeight(null, $options);

        $actual = $sut->getInputSpecification();

        foreach ($expect as $key => $val) {
            static::assertEquals($val, $actual[$key]);
        }
    }

    /**
     * @return true[][][]
     *
     * @psalm-return list{array{options: array{required: true, allow_empty: true}, expect: array{required: true, allow_empty: true}}}
     */
    public function dpTestGetInputSpecificationOptions(): array
    {
        return [
            [
                'options' => [
                    'required' => true,
                    'allow_empty' => true,
                ],
                'expect' => [
                    'required' => true,
                    'allow_empty' => true,
                ]
            ]
        ];
    }
}
