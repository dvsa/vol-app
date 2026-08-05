<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Data;

use Common\Service\Data\LicenceOperatingCentre;
use Common\Service\Data\Licence as LicenceDataService;
use Mockery as m;

/**
 * Class LicenceTest
 * @package OlcsTest\Service\Data
 */
final class LicenceOperatingCentreTest extends AbstractDataServiceTestCase
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

    #[\PHPUnit\Framework\Attributes\Group('licenceOperatingCentreTest')]
    public function testGetId(): void
    {
        $licenceId = 110;

        $this->licenceDataService->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn($licenceId);

        $this->assertEquals($licenceId, $this->sut->getId());
    }

    #[\PHPUnit\Framework\Attributes\Group('licenceOperatingCentreTest')]
    #[\PHPUnit\Framework\Attributes\DataProvider('providerOutputType')]
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
        $this->assertStringContainsString('a1', (string) $result[1]);

        if ($outputType == LicenceOperatingCentre::OUTPUT_TYPE_FULL) {
            $this->assertStringContainsString('a2', (string) $result[1]);
            $this->assertStringContainsString('a3', (string) $result[1]);
            $this->assertStringContainsString('pc', (string) $result[1]);
        } else {
            $this->assertStringContainsString('town', (string) $result[1]);
        }

        //test data is cached
        $result = $this->sut->fetchListOptions($licenceId);

        $this->assertCount(1, $result);
        $this->assertStringContainsString('a1', (string) $result[1]);

        if ($outputType == LicenceOperatingCentre::OUTPUT_TYPE_FULL) {
            $this->assertStringContainsString('a2', (string) $result[1]);
            $this->assertStringContainsString('a3', (string) $result[1]);
            $this->assertStringContainsString('pc', (string) $result[1]);
        } else {
            $this->assertStringContainsString('town', (string) $result[1]);
        }
    }

    /**
     * @return \Iterator<(int | string), array<int>>
     *
     * @psalm-return list{list{1}, list{2}}
     */
    public static function providerOutputType(): \Iterator
    {
        yield [LicenceOperatingCentre::OUTPUT_TYPE_FULL];
        yield [LicenceOperatingCentre::OUTPUT_TYPE_PARTIAL];
    }

    #[\PHPUnit\Framework\Attributes\Group('licenceOperatingCentreTest')]
    public function testSetOutputType(): void
    {
        $this->sut->setOutputType(LicenceOperatingCentre::OUTPUT_TYPE_FULL);

        $this->assertEquals(LicenceOperatingCentre::OUTPUT_TYPE_FULL, $this->sut->getOutputType());
    }
}
