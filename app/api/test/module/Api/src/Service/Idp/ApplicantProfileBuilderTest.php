<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Idp;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\TradingName;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\FinancialStandingHelperService;
use Dvsa\Olcs\Api\Service\Idp\ApplicantProfileBuilder;
use PHPUnit\Framework\TestCase;
use Mockery as m;

final class ApplicantProfileBuilderTest extends TestCase
{
    private m\MockInterface $helper;
    private ApplicantProfileBuilder $sut;

    protected function tearDown(): void
    {
        m::close();
    }

    protected function setUp(): void
    {
        $this->helper = m::mock(FinancialStandingHelperService::class);
        $this->sut = new ApplicantProfileBuilder($this->helper);
    }

    public function testBuildsTheFullEventAProfile(): void
    {
        $application = $this->givenApplication(
            totAuthVehicles: 10,
            otherLicenceVehicles: [5],
            otherApplicationVehicles: [2],
            tradingNames: ['Speedy Freight']
        );

        $this->helper->shouldReceive('getRequiredFinance')->with($application)->once()->andReturn(94600);

        $this->assertSame(
            [
                'organisation_name' => 'Test Haulage Ltd',
                'trading_name' => 'Speedy Freight',
                'business_type' => 'Registered Company',
                'licence_type' => 'Standard National',
                'vehicles_requested' => 17,
                'required_finance' => 94600.0,
            ],
            $this->sut->build($application)
        );
    }

    /** vehicles_requested must span the same set getRequiredFinance() sums over. */
    public function testVehiclesRequestedSpansLicencesAndOtherApplications(): void
    {
        $application = $this->givenApplication(
            totAuthVehicles: 1,
            otherLicenceVehicles: [2, 3],
            otherApplicationVehicles: [4, 5],
            tradingNames: []
        );

        $this->helper->shouldReceive('getRequiredFinance')->andReturn(0);

        $this->assertSame(15, $this->sut->build($application)['vehicles_requested']);
    }

    public function testTradingNameIsNullWhenTheLicenceHasNone(): void
    {
        $application = $this->givenApplication(1, [], [], []);
        $this->helper->shouldReceive('getRequiredFinance')->andReturn(0);

        $this->assertNull($this->sut->build($application)['trading_name']);
    }

    public function testMultipleTradingNamesAreJoinedAndDeduplicated(): void
    {
        $application = $this->givenApplication(1, [], [], ['Alpha', 'Beta', 'Alpha', '  ']);
        $this->helper->shouldReceive('getRequiredFinance')->andReturn(0);

        $this->assertSame('Alpha, Beta', $this->sut->build($application)['trading_name']);
    }

    /** Description is preferred; the refdata id is the fallback when none is set. */
    public function testFallsBackToRefDataIdWhenDescriptionIsEmpty(): void
    {
        $application = $this->givenApplication(1, [], [], [], businessTypeDescription: '');
        $this->helper->shouldReceive('getRequiredFinance')->andReturn(0);

        $this->assertSame('org_t_rc', $this->sut->build($application)['business_type']);
    }

    /**
     * @param int[] $otherLicenceVehicles
     * @param int[] $otherApplicationVehicles
     * @param string[] $tradingNames
     */
    private function givenApplication(
        int $totAuthVehicles,
        array $otherLicenceVehicles,
        array $otherApplicationVehicles,
        array $tradingNames,
        string $businessTypeDescription = 'Registered Company'
    ): m\MockInterface {
        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getName')->andReturn('Test Haulage Ltd');
        $organisation->shouldReceive('getType')->andReturn(
            $this->refData('org_t_rc', $businessTypeDescription)
        );

        $licence = m::mock(Licence::class);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getTradingNames')->andReturn(
            new ArrayCollection(array_map(function (string $name): m\MockInterface {
                $tradingName = m::mock(TradingName::class);
                $tradingName->shouldReceive('getName')->andReturn($name);
                return $tradingName;
            }, $tradingNames))
        );

        $application = m::mock(Application::class);
        $application->shouldReceive('getLicence')->andReturn($licence);
        $application->shouldReceive('getLicenceType')->andReturn(
            $this->refData('ltyp_sn', 'Standard National')
        );
        $application->shouldReceive('getTotAuthVehicles')->andReturn($totAuthVehicles);
        $application->shouldReceive('getOtherActiveLicencesForOrganisation')->andReturn(
            array_map(function (int $count): m\MockInterface {
                $other = m::mock(Licence::class);
                $other->shouldReceive('getTotAuthVehicles')->andReturn($count);
                return $other;
            }, $otherLicenceVehicles)
        );

        $this->helper->shouldReceive('getOtherNewApplications')->with($application)->andReturn(
            array_map(function (int $count): m\MockInterface {
                $other = m::mock(Application::class);
                $other->shouldReceive('getTotAuthVehicles')->andReturn($count);
                return $other;
            }, $otherApplicationVehicles)
        );

        return $application;
    }

    private function refData(string $id, string $description): m\MockInterface
    {
        $refData = m::mock(RefData::class);
        $refData->shouldReceive('getId')->andReturn($id);
        $refData->shouldReceive('getDescription')->andReturn($description);

        return $refData;
    }
}
