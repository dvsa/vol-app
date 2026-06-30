<?php

namespace CommonTest\Common\Service\Data;

use Common\Service\Data\LicenceOperatingCentre;
use Common\Service\Data\Licence as LicenceDataService;
use Mockery as m;

/**
 * Class LicenceTest
 * @package OlcsTest\Service\Data
 */
class LicenceOperatingCentreTest extends AbstractDataServiceTestCase
{
    /** @var LicenceOperatingCentre */
    private $sut;

    /** @var LicenceDataService */
    protected $licenceDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->licenceDataService = m::mock(LicenceDataService::class);

        $this->sut = new LicenceOperatingCentre(
            $this->abstractDataServiceServices,
            $this->licenceDataService
        );
    }

    /**
     * @group licenceOperatingCentreTest
     */
    public function testGetId(): void
    {
        $licenceId = 110;

        $this->licenceDataService->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn($licenceId);

        $this->assertEquals($licenceId, $this->sut->getId());
    }

    /**
     * @group licenceOperatingCentreTest
     * @dataProvider providerOutputType
     */
    public function testFetchListOptions($outputType): void
    {
        $this->sut->setOutputType($outputType);

        $licenceId = 110;
        $licenceData = [
            'operatingCentres' => [
                'operatingCentre' => [
                    'operatingCentre' => [
                        'id' => 1,
                        'address' => [
                            'addressLine1' => 'a1',
                            'addressLine2' => 'a2',
                            'addressLine3' => 'a3',
                            'addressLine4' => 'a4',
                            'town' => 'town',
                            'postcode' => 'pc',
                        ]
                    ]
                ]
            ]
        ];

        $this->licenceDataService->shouldReceive('getId')
            ->times(3)
            ->withNoArgs()
            ->andReturn($licenceId)
            ->shouldReceive('fetchOperatingCentreData')
            ->once()
            ->with($licenceId)
            ->andReturn($licenceData);

        $result = $this->sut->fetchListOptions($licenceId);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('a1', $result[1]);

        if ($outputType == LicenceOperatingCentre::OUTPUT_TYPE_FULL) {
            $this->assertStringContainsString('a2', $result[1]);
            $this->assertStringContainsString('a3', $result[1]);
            $this->assertStringContainsString('pc', $result[1]);
        } else {
            $this->assertStringContainsString('town', $result[1]);
        }

        //test data is cached
        $result = $this->sut->fetchListOptions($licenceId);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('a1', $result[1]);

        if ($outputType == LicenceOperatingCentre::OUTPUT_TYPE_FULL) {
            $this->assertStringContainsString('a2', $result[1]);
            $this->assertStringContainsString('a3', $result[1]);
            $this->assertStringContainsString('pc', $result[1]);
        } else {
            $this->assertStringContainsString('town', $result[1]);
        }
    }

    /**
     * @return int[][]
     *
     * @psalm-return list{list{1}, list{2}}
     */
    public function providerOutputType(): array
    {
        return [
            [LicenceOperatingCentre::OUTPUT_TYPE_FULL],
            [LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL]
        ];
    }

    /**
     * @group licenceOperatingCentreTest
     */
    public function testSetOutputType(): void
    {
        $this->sut->setOutputType(LicenceOperatingCentre::OUTPUT_TYPE_FULL);

        $this->assertEquals(LicenceOperatingCentre::OUTPUT_TYPE_FULL, $this->sut->getOutputType());
    }
}
