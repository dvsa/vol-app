<?php

namespace OlcsTest\Service\Data;

use Common\Service\Data\ApplicationOperatingCentre;
use Common\Service\Data\LicenceOperatingCentre;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Data\OcContextListDataService;
use Mockery as m;

/**
 * Class OcContextListDataService Test
 * @package CommonTest\Service
 */
class OcContextListDataServiceTest extends MockeryTestCase
{
    /**
     * Holds the SUT
     *
     * @var OcContextListDataService
     */
    private $sut;

    /**
     * @var LicenceOperatingCentre
     */
    private $licenceOperatingCentreDataService;

    /**
     * @var ApplicationOperatingCentre
     */
    private $applicationOperatingCentreDataService;

    /**
     * Setup the sut
     */
    #[\Override]
    protected function setUp(): void
    {
        $this->licenceOperatingCentreDataService = m::mock(LicenceOperatingCentre::class);
        $this->applicationOperatingCentreDataService = m::mock(ApplicationOperatingCentre::class);

        $this->sut = new OcContextListDataService(
            $this->licenceOperatingCentreDataService,
            $this->applicationOperatingCentreDataService
        );
    }


    public function testFetchListOptionsApplicationContext(): void
    {
        $useGroups = false;

        $applicationOperatingCentres = [
            'operatingCentres' => [
                0 => [],
                1 => []
            ]
        ];

        $this->applicationOperatingCentreDataService->shouldReceive('fetchListOptions')->andReturn($applicationOperatingCentres);
        $appOptions = $this->sut->fetchListOptions('application', $useGroups);

        $this->assertEquals($applicationOperatingCentres, $appOptions);
    }

    public function testFetchListOptionsLicenceContext(): void
    {
        $useGroups = false;

        $licenceOperatingCentres = [
            'operatingCentres' => [
                0 => []
            ]
        ];

        $this->licenceOperatingCentreDataService->shouldReceive('fetchListOptions')->andReturn($licenceOperatingCentres);
        $lOptions = $this->sut->fetchListOptions('licence', $useGroups);

        $this->assertEquals($licenceOperatingCentres, $lOptions);
    }

    public function testFetchListOptionsNullContext(): void
    {
        $useGroups = false;

        $options = $this->sut->fetchListOptions('', $useGroups);

        $this->assertEmpty($options);
    }
}
