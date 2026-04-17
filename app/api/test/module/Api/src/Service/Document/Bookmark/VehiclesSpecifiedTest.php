<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\VehiclesSpecified;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * VehiclesSpecified bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class VehiclesSpecifiedTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new VehiclesSpecified();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoVehiclesSpecified(): void
    {
        $bookmark = new VehiclesSpecified();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithGoodsVehiclesSpecified(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\VehiclesSpecified::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_3CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'licenceVehicles' => [
                    [
                        'vehicle' => [
                            'vrm' => 'VRM1',
                            'platedWeight' => 900
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'VRM4',
                            'platedWeight' => 1000
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'VRM3',
                            'platedWeight' => 1200
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'VRM2',
                            'platedWeight' => 2000
                        ]
                    ],
                ],
                'goodsOrPsv' => [
                    'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'Registration mark',
            'BOOKMARK2' => 'Plated weight',
            'BOOKMARK3' => 'To continue to be specified on licence (Y/N)'
        ];
        $row1 = [
            'BOOKMARK1' => 'VRM1',
            'BOOKMARK2' => 900,
            'BOOKMARK3' => ''
        ];
        $row2 = [
            'BOOKMARK1' => 'VRM2',
            'BOOKMARK2' => 2000,
            'BOOKMARK3' => ''
        ];
        $row3 = [
            'BOOKMARK1' => 'VRM3',
            'BOOKMARK2' => 1200,
            'BOOKMARK3' => ''
        ];
        $row4 = [
            'BOOKMARK1' => 'VRM4',
            'BOOKMARK2' => 1000,
            'BOOKMARK3' => ''
        ];
        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => '',
            'BOOKMARK3' => ''
        ];

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with('snippet', $header)
            ->andReturn('header|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row1)
            ->andReturn('row1|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row2)
            ->andReturn('row2|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row3)
            ->andReturn('row3|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row4)
            ->andReturn('row4|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->times(11)
            ->getMock();

        $bookmark->setParser($mockParser);

        $rendered = 'header|row1|row2|row3|row4|' . str_repeat('emptyrow|', 11);
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }

    public function testRenderWithPsvVehiclesSpecified(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\VehiclesSpecified::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_2CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'licenceVehicles' => [
                    [
                        'vehicle' => [
                            'vrm' => 'VRM1',
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'VRM4',
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'VRM3',
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'VRM4',
                        ]
                    ],
                ],
                'goodsOrPsv' => [
                    'id' => Licence::LICENCE_CATEGORY_PSV
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'Registration mark',
            'BOOKMARK2' => 'To continue to be specified on licence (Y/N)'
        ];
        $row1 = [
            'BOOKMARK1' => 'VRM1',
            'BOOKMARK2' => ''
        ];
        $row2 = [
            'BOOKMARK1' => 'VRM3',
            'BOOKMARK2' => ''
        ];
        $row3 = [
            'BOOKMARK1' => 'VRM4',
            'BOOKMARK2' => ''
        ];
        $row4 = [
            'BOOKMARK1' => 'VRM4',
            'BOOKMARK2' => ''
        ];
        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => '',
            'BOOKMARK3' => ''
        ];

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with('snippet', $header)
            ->andReturn('header|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row1)
            ->andReturn('row1|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row2)
            ->andReturn('row2|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row3)
            ->andReturn('row3|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row4)
            ->andReturn('row4|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->times(11)
            ->getMock();

        $bookmark->setParser($mockParser);

        $rendered = 'header|row1|row2|row3|row4|' . str_repeat('emptyrow|', 11);
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }
}
