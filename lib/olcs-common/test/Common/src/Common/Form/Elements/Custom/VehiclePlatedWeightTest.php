<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\VehiclePlatedWeight;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Elements\Custom\VehiclePlatedWeight::class)]
final class VehiclePlatedWeightTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        /** @var VehiclePlatedWeight $sut */
        $sut = m::mock(VehiclePlatedWeight::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        $this->assertEquals('unit_Name', $actual['name']);
        $this->assertSame([
            \Laminas\Validator\Digits::class,
            \Laminas\Validator\Between::class,
        ], array_map(
            static fn($item) => $item['name'],
            $actual['validators']
        ));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetInputSpecificationOptions')]
    public function testGetInputSpecificationOptions($options, $expect): void
    {
        /** @var VehiclePlatedWeight $sut */
        $sut = new VehiclePlatedWeight(null, $options);

        $actual = $sut->getInputSpecification();

        foreach ($expect as $key => $val) {
            $this->assertEquals($val, $actual[$key]);
        }
    }

    /**
     * @return \Iterator<(int | string), array<array<true>>>
     *
     * @psalm-return list{array{options: array{required: true, allow_empty: true}, expect: array{required: true, allow_empty: true}}}
     */
    public static function dpTestGetInputSpecificationOptions(): \Iterator
    {
        yield [
            'options' => [
                'required' => true,
                'allow_empty' => true,
            ],
            'expect' => [
                'required' => true,
                'allow_empty' => true,
            ]
        ];
    }
}
