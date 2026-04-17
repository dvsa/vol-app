<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceOperatingCentres;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Licence Operating Centres bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceOperatingCentresTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new LicenceOperatingCentres();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoLicenceOperatingCentres(): void
    {
        $bookmark = new LicenceOperatingCentres();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithGoodsLicenceOperatingCentres(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceOperatingCentres::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_2CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'operatingCentres' => [
                    [
                        'operatingCentre' => [
                            'address' => [
                                'addressLine1' => 'bl1',
                                'addressLine2' => 'bl2',
                                'addressLine3' => 'bl3',
                                'addressLine4' => 'bl4',
                                'town' => 'town',
                                'postcode' => 'postcode',
                            ]
                        ],
                        'noOfVehiclesRequired' => 1,
                        'noOfTrailersRequired' => 2
                    ],
                    [
                        'operatingCentre' => [
                            'address' => [
                                'addressLine1' => 'al1',
                                'addressLine2' => 'al2',
                                'addressLine3' => 'al3',
                                'addressLine4' => 'al4',
                                'town' => 'town',
                                'postcode' => 'postcode',
                            ]
                        ],
                        'noOfVehiclesRequired' => 1,
                        'noOfTrailersRequired' => 2
                    ],
                    [
                        'operatingCentre' => [
                            'address' => [
                                'addressLine1' => 'cl1',
                                'addressLine2' => 'cl2',
                                'addressLine3' => 'cl3',
                                'addressLine4' => 'cl4',
                                'town' => 'town',
                                'postcode' => 'postcode',
                            ]
                        ],
                        'noOfVehiclesRequired' => 1,
                        'noOfTrailersRequired' => 2
                    ],
                    [
                        'operatingCentre' => [
                            'address' => [
                                'addressLine1' => 'cl1',
                                'addressLine2' => 'cl2',
                                'addressLine3' => 'cl3',
                                'addressLine4' => 'cl4',
                                'town' => 'town',
                                'postcode' => 'postcode',
                            ]
                        ],
                        'noOfVehiclesRequired' => 1,
                        'noOfTrailersRequired' => 2
                    ],
                ],
                'goodsOrPsv' => [
                    'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'Operating centre address (insert full postcode if not shown)',
            'BOOKMARK2' => 'Vehicles/trailers authorised'
        ];
        $row1 = [
            'BOOKMARK1' => "al1\nal2\nal3\nal4\ntown\npostcode",
            'BOOKMARK2' => "Maximum number of vehicles :  1\nMaximum number of trailers :  2"
        ];
        $row2 = [
            'BOOKMARK1' => "bl1\nbl2\nbl3\nbl4\ntown\npostcode",
            'BOOKMARK2' => "Maximum number of vehicles :  1\nMaximum number of trailers :  2"
        ];
        $row3 = [
            'BOOKMARK1' => "cl1\ncl2\ncl3\ncl4\ntown\npostcode",
            'BOOKMARK2' => "Maximum number of vehicles :  1\nMaximum number of trailers :  2"
        ];
        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => ''
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
            ->twice()
            ->shouldReceive('replace')
            ->with('snippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->times(2)
            ->getMock();

        $bookmark->setParser($mockParser);

        $rendered = 'header|row1|row2|row3|row3|emptyrow|emptyrow|';
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }

    public function testRenderWithPsvLicenceOperatingCentres(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceOperatingCentres::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_2CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'operatingCentres' => [
                    [
                        'operatingCentre' => [
                            'address' => [
                                'addressLine1' => 'al1',
                                'addressLine2' => 'al2',
                                'addressLine3' => 'al3',
                                'addressLine4' => 'al4',
                                'town' => 'town',
                                'postcode' => 'postcode',
                            ]
                        ],
                        'noOfVehiclesRequired' => 1
                    ],
                ],
                'goodsOrPsv' => [
                    'id' => Licence::LICENCE_CATEGORY_PSV
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'Operating centre address (insert full postcode if not shown)',
            'BOOKMARK2' => 'Vehicles authorised'
        ];
        $row = [
            'BOOKMARK1' => "al1\nal2\nal3\nal4\ntown\npostcode",
            'BOOKMARK2' => "Maximum number of vehicles :  1"
        ];
        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => ''
        ];

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with('snippet', $header)
            ->andReturn('header|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row)
            ->andReturn('row|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->times(5)
            ->getMock();

        $bookmark->setParser($mockParser);

        $rendered = 'header|row|emptyrow|emptyrow|emptyrow|emptyrow|emptyrow|';
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }
}
