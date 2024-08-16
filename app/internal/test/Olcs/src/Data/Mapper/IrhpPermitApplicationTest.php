<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpPermitApplicationTest
 */
class IrhpPermitApplicationTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Data\Mapper\IrhpPermitApplication();
    }

    public function testMapSectors()
    {
        $data = [
            'count' => 3,
            'results' => [
                [
                    'description' => 'Beverages and tobacco, products of agriculture, hunting and forests, fish and other fishing products',
                    'displayOrder' => 20,
                    'id' => 1,
                    'name' => 'Food products',
                ],
                [
                    'description' => '',
                    'displayOrder' => 30,
                    'id' => 2,
                    'name' => 'Mail and Parcels',
                ],
                [
                    'description' => 'Beverages and tobacco, products of agriculture, hunting and forests, fish and other fishing products',
                    'displayOrder' => 40,
                    'id' => 3,
                    'name' => 'Textiles',
                ]
            ]

        ];

        $expected = [
            [
                'value' => 1,
                'label' => 'Food products',
                'selected' => false
            ],
            [
                'value' => 2,
                'label' => 'Mail and Parcels',
                'selected' => false
            ],
            [
                'value' => 3,
                'label' => 'Textiles',
                'selected' => true,
            ]
        ];

        $this->assertSame($expected, $this->sut->mapSectors($data, 3));
    }
}
