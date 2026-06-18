<?php

/**
 * Sum Context Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Form\Elements\Validators;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Form\Elements\Validators\SumContext;

/**
 * Sum Context Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SumContextTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new SumContext();
    }

    /**
     * @dataProvider providerIsValid
     */
    public function testIsValid($min, $max, $context, $expected): void
    {
        $this->sut->setMin($min);
        $this->sut->setMax($max);

        $this->assertEquals($expected, $this->sut->isValid(null, $context));
    }

    /**
     * @return ((float|int|null)[]|bool|int|null)[][]
     *
     * @psalm-return array{'Hours Per Week invalid example with all contexts are null': list{1, null, array{hoursMon: null, hoursTue: null, hoursWed: null, hoursThu: null, hoursFri: null, hoursSat: null, hoursSun: null}, false}, 'Hours Per Week invalid example with a zero digit in any 1 context': list{1, null, array{hoursMon: null, hoursTue: 0, hoursWed: null, hoursThu: null, hoursFri: null, hoursSat: null, hoursSun: null}, false}, 'Hours Per Week invalid example with a zero digit in any other context': list{1, null, array{hoursMon: null, hoursTue: null, hoursWed: null, hoursThu: null, hoursFri: null, hoursSat: null, hoursSun: 0}, false}, 'Hours Per Week valid example with one float value, rest are null': list{1, null, array{hoursMon: null, hoursTue: null, hoursWed: null, hoursThu: null, hoursFri: null, hoursSat: null, hoursSun: float}, true}, 'Hours Per Week valid example with two float values, rest are null': list{1, null, array{hoursMon: null, hoursTue: null, hoursWed: float, hoursThu: null, hoursFri: null, hoursSat: null, hoursSun: float}, true}, 'Hours Per Week valid example with all float value, none are null': list{1, null, array{hoursMon: float, hoursTue: 8, hoursWed: 12, hoursThu: float, hoursFri: float, hoursSat: float, hoursSun: float}, true}, 0: list{null, 10, array{foo: 3, bar: 3}, true}, 1: list{null, 10, array{foo: 3, bar: 3, cake: 3, box: 3}, false}, 2: list{null, 10, array{foo: 3, bar: 3, cake: 3, box: 1}, true}, 3: list{10, null, array{foo: 3, bar: 3, cake: 3, box: 1}, true}, 4: list{10, null, array{foo: 3, bar: 3, cake: 3}, false}, 5: list{10, null, array{foo: 3, bar: 3, cake: 3, box: 3}, true}, 6: list{1, 5, array{foo: 1}, true}, 7: list{1, 5, array{foo: 5}, true}, 8: list{1, 5, array{foo: 0}, false}, 9: list{1, 5, array{foo: 6}, false}}
     */
    public function providerIsValid(): array
    {
        return [
            // Data sets that match requirements for the Hours Per Week fieldset
            // which uses this SumContext validator.
            'Hours Per Week invalid example with all contexts are null' => [
                1,
                null,
                [
                    'hoursMon' => null,
                    'hoursTue' => null,
                    'hoursWed' => null,
                    'hoursThu' => null,
                    'hoursFri' => null,
                    'hoursSat' => null,
                    'hoursSun' => null,
                ],
                false,
            ],
            'Hours Per Week invalid example with a zero digit in any 1 context' => [
                1,
                null,
                [
                    'hoursMon' => null,
                    'hoursTue' => 0,
                    'hoursWed' => null,
                    'hoursThu' => null,
                    'hoursFri' => null,
                    'hoursSat' => null,
                    'hoursSun' => null,
                ],
                false,
            ],
            'Hours Per Week invalid example with a zero digit in any other context' => [
                1,
                null,
                [
                    'hoursMon' => null,
                    'hoursTue' => null,
                    'hoursWed' => null,
                    'hoursThu' => null,
                    'hoursFri' => null,
                    'hoursSat' => null,
                    'hoursSun' => 0,
                ],
                false,
            ],
            'Hours Per Week valid example with one float value, rest are null' => [
                1,
                null,
                [
                    'hoursMon' => null,
                    'hoursTue' => null,
                    'hoursWed' => null,
                    'hoursThu' => null,
                    'hoursFri' => null,
                    'hoursSat' => null,
                    'hoursSun' => 1.4,
                ],
                true,
            ],
            'Hours Per Week valid example with two float values, rest are null' => [
                1,
                null,
                [
                    'hoursMon' => null,
                    'hoursTue' => null,
                    'hoursWed' => 1.3,
                    'hoursThu' => null,
                    'hoursFri' => null,
                    'hoursSat' => null,
                    'hoursSun' => 0.4,
                ],
                true,
            ],
            'Hours Per Week valid example with all float value, none are null' => [
                1,
                null,
                [
                    'hoursMon' => 3.5,
                    'hoursTue' => 8,
                    'hoursWed' => 12,
                    'hoursThu' => 4.5,
                    'hoursFri' => 2.7,
                    'hoursSat' => 3.92,
                    'hoursSun' => 0.4,
                ],
                true,
            ],

            // Random datasets
            [
                null,
                10,
                [
                    'foo' => 3,
                    'bar' => 3
                ],
                true
            ],
            [
                null,
                10,
                [
                    'foo' => 3,
                    'bar' => 3,
                    'cake' => 3,
                    'box' => 3
                ],
                false
            ],
            [
                null,
                10,
                [
                    'foo' => 3,
                    'bar' => 3,
                    'cake' => 3,
                    'box' => 1
                ],
                true
            ],
            [
                10,
                null,
                [
                    'foo' => 3,
                    'bar' => 3,
                    'cake' => 3,
                    'box' => 1
                ],
                true
            ],
            [
                10,
                null,
                [
                    'foo' => 3,
                    'bar' => 3,
                    'cake' => 3
                ],
                false
            ],
            [
                10,
                null,
                [
                    'foo' => 3,
                    'bar' => 3,
                    'cake' => 3,
                    'box' => 3
                ],
                true
            ],
            [
                1,
                5,
                [
                    'foo' => 1
                ],
                true
            ],
            [
                1,
                5,
                [
                    'foo' => 5
                ],
                true
            ],
            [
                1,
                5,
                [
                    'foo' => 0
                ],
                false
            ],
            [
                1,
                5,
                [
                    'foo' => 6
                ],
                false
            ]
        ];
    }
}
