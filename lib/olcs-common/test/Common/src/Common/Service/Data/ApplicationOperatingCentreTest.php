<?php

namespace CommonTest\Common\Service\Data;

use Common\Service\Data\Application as ApplicationDataService;
use Common\Service\Data\ApplicationOperatingCentre;
use Mockery as m;

/**
 * Class ApplicationOperatingCentre Test
 * @package CommonTest\Service
 */
class ApplicationOperatingCentreTest extends AbstractDataServiceTestCase
{
    /** @var ApplicationOperatingCentre */
    private $sut;

    /** @var ApplicationDataService */
    protected $applicationDataService;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->applicationDataService = m::mock(ApplicationDataService::class);

        $this->sut = new ApplicationOperatingCentre(
            $this->abstractDataServiceServices,
            $this->applicationDataService
        );
    }

    public function testGetId(): void
    {
        $id = 1;

        $this->applicationDataService->shouldReceive('getId')
            ->once()
            ->andReturn($id);

        $this->assertEquals($id, $this->sut->getId());
    }

    public function testFetchListOptionsFullAddress(): void
    {
        $context = 'application';
        $useGroups = false;

        $mockData = [
            'operatingCentres' => [
                0 => [
                    'operatingCentre' => [
                        'id' => 99,
                        'address' => [
                            'addressLine1' => 'a1',
                            'addressLine2' => 'a2',
                            'addressLine3' => 'a3',
                            'addressLine4' => 'a4',
                            'town' => 'anytown',
                            'postcode' => 'pc'
                        ]
                    ]
                ]
            ]
        ];

        $id = 1;

        $this->applicationDataService->shouldReceive('getId')
            ->andReturn($id)
            ->shouldReceive('fetchOperatingCentreData')
            ->with($id)
            ->andReturn($mockData);

        $output = $this->sut->fetchListOptions($context, $useGroups);

        $this->assertEquals([99 => 'a1, a2, a3, a4, anytown, pc'], $output);
    }

    public function testFetchListOptionsPartialAddress(): void
    {
        $context = 'application';
        $useGroups = false;

        $mockData = [
            'operatingCentres' => [
                0 => [
                    'operatingCentre' => [
                        'id' => 99,
                        'address' => [
                            'addressLine1' => 'a1',
                            'addressLine2' => 'a2',
                            'addressLine3' => 'a3',
                            'addressLine4' => 'a4',
                            'town' => 'anytown',
                            'postcode' => 'pc'
                        ]
                    ]
                ]
            ]
        ];

        $id = 1;

        $this->applicationDataService->shouldReceive('getId')
            ->andReturn($id)
            ->shouldReceive('fetchOperatingCentreData')
            ->with($id)
            ->andReturn($mockData);

        $this->sut->setOutputType(ApplicationOperatingCentre::OUTPUT_TYPE_PARTIAL);

        $output = $this->sut->fetchListOptions($context, $useGroups);

        $this->assertEquals([99 => 'a1, anytown'], $output);
    }
}
