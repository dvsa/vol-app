<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\OperatingCentres;
use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;
use Mockery as m;

/**
 * Operating Centres test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new OperatingCentres();
        $query = $bookmark->getQuery(['licence' => 7]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoData(): void
    {
        $parser = new RtfParser();
        $bookmark = new OperatingCentres();
        $bookmark->setData([]);
        $bookmark->setParser($parser);

        $result = $bookmark->render();

        $this->assertEquals('', $result);
    }

    public static function dpRenderWithGoodsLicence(): array
    {
        return [
            [
                'vehicleTypeId' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthLgvVehicles' => null,
                'expectedTabVeh' => 'Vehicles',
            ],
            [
                'vehicleTypeId' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthLgvVehicles' => 0,
                'expectedTabVeh' => 'Heavy goods vehicles',
            ],
            [
                'vehicleTypeId' => RefData::APP_VEHICLE_TYPE_MIXED,
                'totAuthLgvVehicles' => 1,
                'expectedTabVeh' => 'Heavy goods vehicles',
            ],
            [
                'vehicleTypeId' => RefData::APP_VEHICLE_TYPE_HGV,
                'totAuthLgvVehicles' => null,
                'expectedTabVeh' => 'Vehicles',
            ],
            [
                'vehicleTypeId' => RefData::APP_VEHICLE_TYPE_LGV,
                'totAuthLgvVehicles' => 10,
                'expectedTabVeh' => 'Vehicles',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderWithGoodsLicence')]
    public function testRenderWithGoodsLicence(mixed $vehicleTypeId, mixed $totAuthLgvVehicles, mixed $expectedTabVeh): void
    {
        $data = [
            'id' => 100,
            'goodsOrPsv' => [
                'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            ],
            'vehicleType' => [
                'id' => $vehicleTypeId,
            ],
            'totAuthLgvVehicles' => $totAuthLgvVehicles,
            'operatingCentres' => [
                [
                    'noOfVehiclesRequired' => 10,
                    'noOfTrailersRequired' => 5,
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Address 1',
                            'addressLine2' => 'Address 2'
                        ],
                        'conditionUndertakings' => [
                            [
                                'conditionType' => [
                                    'id' => ConditionUndertakingEntity::TYPE_CONDITION
                                ],
                                'attachedTo' => [
                                    'id' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
                                ],
                                'isFulfilled' => 'N',
                                'isDraft' => 'N',
                                'notes' => 'condition 1',
                                'licence' => [
                                    'id' => 100
                                ]
                            ], [
                                'conditionType' => [
                                    'id' => ConditionUndertakingEntity::TYPE_UNDERTAKING
                                ],
                                'attachedTo' => [
                                    'id' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
                                ],
                                'isFulfilled' => 'N',
                                'isDraft' => 'N',
                                'notes' => 'undertaking 1',
                                'licence' => [
                                    'id' => 100
                                ]
                            ], [
                                'conditionType' => [
                                    'id' => ConditionUndertakingEntity::TYPE_UNDERTAKING
                                ],
                                'attachedTo' => [
                                    'id' => ConditionUndertakingEntity::ATTACHED_TO_OPERATING_CENTRE
                                ],
                                'isFulfilled' => 'N',
                                'isDraft' => 'N',
                                'notes' => 'undertaking 2',
                                'licence' => [
                                    'id' => 100
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $parser = m::mock(RtfParser::class)->makePartial();

        $conditionUndertakings = "Conditions\n\n1).\tcondition 1\n\n" .
            "Undertakings\n\n1).\tundertaking 1\n\n2).\tundertaking 2";

        $expectedRow = [
            'TAB_OC_ADD' => "Address 1\nAddress 2",
            'TAB_VEH' => $expectedTabVeh,
            'TAB_OC_VEH' => 10,
            'TAB_TRAILER' => 'Trailers',
            'TAB_OC_TRAILER' => 5,
            'TAB_OC_CONDS_UNDERS' => $conditionUndertakings
        ];

        $parser->expects('replace')
            ->with('snippet', $expectedRow)
            ->andReturn('foo');

        $bookmark = $this->createPartialMock(OperatingCentres::class, ['getSnippet']);

        $bookmark->expects($this->atLeastOnce())
            ->method('getSnippet')
            ->willReturn('snippet');

        $bookmark->setData($data);
        $bookmark->setParser($parser);

        $result = $bookmark->render();
        $this->assertEquals('foo', $result);
    }

    public function testRenderWithPsvLicence(): void
    {
        $data = [
            'id' => 123,
            'goodsOrPsv' => [
                'id' => Licence::LICENCE_CATEGORY_PSV,
            ],
            'vehicleType' => [
                'id' => RefData::APP_VEHICLE_TYPE_PSV,
            ],
            'operatingCentres' => [
                [
                    'noOfVehiclesRequired' => 10,
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => 'Address 1',
                            'addressLine2' => 'Address 2'
                        ],
                        'conditionUndertakings' => []
                    ]
                ]
            ]
        ];

        $parser = m::mock(RtfParser::class)->makePartial();

        $expectedRow = [
            'TAB_OC_ADD' => "Address 1\nAddress 2",
            'TAB_VEH' => 'Vehicles',
            'TAB_OC_VEH' => 10,
            'TAB_TRAILER' => '',
            'TAB_OC_TRAILER' => '',
            'TAB_OC_CONDS_UNDERS' => ''
        ];

        $parser->expects('replace')
            ->with('snippet', $expectedRow)
            ->andReturn('foo');

        $bookmark = $this->createPartialMock(OperatingCentres::class, ['getSnippet']);

        $bookmark->expects($this->atLeastOnce())
            ->method('getSnippet')
            ->willReturn('snippet');

        $bookmark->setData($data);
        $bookmark->setParser($parser);

        $result = $bookmark->render();
        $this->assertEquals('foo', $result);
    }
}
