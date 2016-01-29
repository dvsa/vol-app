<?php
namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\OperatingCentres as Sut;

/**
 * Operating Centres Mapper Test
 */
class OperatingCentresTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'results' => [
                [
                    'id' => 1,
                    'address' => [
                        'addressLine1' => 'al1',
                        'town' => 'town'
                    ]
                ]
            ]
        ];
        $expected = [
            '1' => 'al1, town'
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }
}
