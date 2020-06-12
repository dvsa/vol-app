<?php

namespace PermitsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Permits\Data\Mapper\ConfirmedUpdatedCountries;

/**
 * ConfirmedUpdatedCountriesTest
 */
class ConfirmedUpdatedCountriesTest extends TestCase
{
    public function testMapFromForm()
    {
        $data = [
            'fields' => [
                'inputDataKey1' => 'inputDataValue1',
                'inputDataKey2' => 'inputDataValue2',
                'countries' => 'ES,CH,DE'
            ]
        ];

        $expected = [
            'inputDataKey1' => 'inputDataValue1',
            'inputDataKey2' => 'inputDataValue2',
            'countries' => [
                'ES',
                'CH',
                'DE'
            ]
        ];

        $confirmedUpdatedCountries = new ConfirmedUpdatedCountries();

        $this->assertEquals(
            $expected,
            $confirmedUpdatedCountries->mapFromForm($data)
        );
    }
}
