<?php

namespace AdminTest\Data\Mapper;

use Admin\Data\Mapper\IrhpPermitStock as Sut;
use PHPUnit\Framework\TestCase;

/**
 * IRHP Mapper Test
 */
class IrhpPermitStockTest extends TestCase
{
    public function testMapCountryOptions()
    {
        $input = [
            [
                'id' => 'PT',
                'countryDesc' => 'Portugal'
            ],
            [
                'id' => 'HU',
                'countryDesc' => 'Hungary'
            ],
            [
                'id' => 'IT',
                'countryDesc' => 'Italy'
            ]
        ];

        $expected = [
            'PT' => 'Portugal',
            'HU' => 'Hungary',
            'IT' => 'Italy'
        ];

        $this->assertEquals($expected, Sut::mapCountryOptions($input));
    }
}
