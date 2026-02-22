<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\FeeBreakdown;

use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\FeeBreakdown\BilateralFeeBreakdownGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * BilateralFeeBreakdownGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class BilateralFeeBreakdownGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $feeTypeRepo = m::mock(FeeTypeRepository::class);

        $bilateralPermitUsageSelection1 = RefData::JOURNEY_SINGLE;
        $countryDesc1 = 'Spain';
        $filteredBilateralRequired1 = [
            IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED => 5,
            IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED => 8,
        ];

        $productReference1StandardApplication = 'PRODUCT_REFERENCE_1_STANDARD_APPLICATION';
        $productReference1StandardIssue = 'PRODUCT_REFERENCE_1_STANDARD_ISSUE';
        $productReference1CabotageApplication = 'PRODUCT_REFERENCE_1_CABOTAGE_APPLICATION';
        $productReference1CabotageIssue = 'PRODUCT_REFERENCE_1_CABOTAGE_ISSUE';

        $applicationFeeType1StandardApplication = m::mock(FeeType::class);
        $applicationFeeType1StandardIssue = m::mock(FeeType::class);
        $feePerPermit1Standard = 45;

        $applicationFeeType1CabotageApplication = m::mock(FeeType::class);
        $applicationFeeType1CabotageIssue = m::mock(FeeType::class);
        $feePerPermit1Cabotage = 60;

        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference1StandardApplication)
            ->andReturn($applicationFeeType1StandardApplication);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference1StandardIssue)
            ->andReturn($applicationFeeType1StandardIssue);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference1CabotageApplication)
            ->andReturn($applicationFeeType1CabotageApplication);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference1CabotageIssue)
            ->andReturn($applicationFeeType1CabotageIssue);

        $irhpPermitApplication1 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication1->shouldReceive('getBilateralPermitUsageSelection')
            ->withNoArgs()
            ->andReturn($bilateralPermitUsageSelection1);
        $country1 = m::mock(Country::class);
        $country1->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn($countryDesc1);
        $irhpPermitStock1 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock1->shouldReceive('getCountry')
            ->withNoArgs()
            ->andReturn($country1);
        $irhpPermitApplication1->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock1);
        $irhpPermitApplication1->shouldReceive('getFilteredBilateralRequired')
            ->withNoArgs()
            ->andReturn($filteredBilateralRequired1);

        $irhpPermitApplication1->shouldReceive('getBilateralFeePerPermit')
            ->andReturnUsing(function ($feeTypes) use (
                $applicationFeeType1StandardApplication,
                $applicationFeeType1StandardIssue,
                $feePerPermit1Standard,
                $applicationFeeType1CabotageApplication,
                $applicationFeeType1CabotageIssue,
                $feePerPermit1Cabotage
            ) {
                $this->assertCount(2, $feeTypes);
                $this->assertArrayHasKey(0, $feeTypes);
                $this->assertArrayHasKey(1, $feeTypes);

                if (
                    $feeTypes[0] === $applicationFeeType1StandardApplication &&
                    $feeTypes[1] === $applicationFeeType1StandardIssue
                ) {
                    return $feePerPermit1Standard;
                } elseif (
                    $feeTypes[0] === $applicationFeeType1CabotageApplication &&
                    $feeTypes[1] === $applicationFeeType1CabotageIssue
                ) {
                    return $feePerPermit1Cabotage;
                }

                throw new \Exception('Unexpected parameters');
            });

        $irhpPermitApplication1->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock1, IrhpPermitApplication::BILATERAL_STANDARD_REQUIRED)
            ->andReturn([$productReference1StandardApplication, $productReference1StandardIssue]);
        $irhpPermitApplication1->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock1, IrhpPermitApplication::BILATERAL_CABOTAGE_REQUIRED)
            ->andReturn([$productReference1CabotageApplication, $productReference1CabotageIssue]);

        $countryDesc2 = 'Morocco';
        $filteredBilateralRequired2 = [
            IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED => 12,
        ];

        $productReference2StandardApplication = 'PRODUCT_REFERENCE_2_MOROCCO_APPLICATION';
        $productReference2StandardIssue = 'PRODUCT_REFERENCE_2_MOROCCO_ISSUE';

        $applicationFeeType2StandardApplication = m::mock(FeeType::class);
        $applicationFeeType2StandardIssue = m::mock(FeeType::class);
        $feePerPermit2Standard = 30;

        $moroccoPeriodNameKey = 'morocco.period.name.key';

        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference2StandardApplication)
            ->andReturn($applicationFeeType2StandardApplication);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with($productReference2StandardIssue)
            ->andReturn($applicationFeeType2StandardIssue);

        $irhpPermitApplication2 = m::mock(IrhpPermitApplication::class);
        $country2 = m::mock(Country::class);
        $country2->shouldReceive('getCountryDesc')
            ->withNoArgs()
            ->andReturn($countryDesc2);
        $irhpPermitStock2 = m::mock(IrhpPermitStock::class);
        $irhpPermitStock2->shouldReceive('getCountry')
            ->withNoArgs()
            ->andReturn($country2);
        $irhpPermitStock2->shouldReceive('getPeriodNameKey')
            ->withNoArgs()
            ->andReturn($moroccoPeriodNameKey);
        $irhpPermitApplication2->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock2);
        $irhpPermitApplication2->shouldReceive('getFilteredBilateralRequired')
            ->withNoArgs()
            ->andReturn($filteredBilateralRequired2);

        $irhpPermitApplication2->shouldReceive('getBilateralFeePerPermit')
            ->andReturnUsing(function ($feeTypes) use (
                $applicationFeeType2StandardApplication,
                $applicationFeeType2StandardIssue,
                $feePerPermit2Standard
            ) {
                $this->assertCount(2, $feeTypes);
                $this->assertArrayHasKey(0, $feeTypes);
                $this->assertArrayHasKey(1, $feeTypes);
                $this->assertSame($applicationFeeType2StandardApplication, $feeTypes[0]);
                $this->assertSame($applicationFeeType2StandardIssue, $feeTypes[1]);

                return $feePerPermit2Standard;
            });

        $irhpPermitApplication2->shouldReceive('getBilateralFeeProductReferences')
            ->with($irhpPermitStock2, IrhpPermitApplication::BILATERAL_MOROCCO_REQUIRED)
            ->andReturn([$productReference2StandardApplication, $productReference2StandardIssue]);

        $irhpPermitApplications = [$irhpPermitApplication1, $irhpPermitApplication2];

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->withNoArgs()
            ->andReturn($irhpPermitApplications);

        $bilateralFeeBreakdownGenerator = new BilateralFeeBreakdownGenerator($feeTypeRepo);

        $expectedResult = [
            [
                'countryName' => 'Morocco',
                'type' => 'morocco.period.name.key',
                'quantity' => 12,
                'total' => 360,
            ],
            [
                'countryName' => 'Spain',
                'type' => 'permits.irhp.range.type.standard.single',
                'quantity' => 5,
                'total' => 225,
            ],
            [
                'countryName' => 'Spain',
                'type' => 'permits.irhp.range.type.cabotage.single',
                'quantity' => 8,
                'total' => 480,
            ],
        ];

        $this->assertEquals(
            $expectedResult,
            $bilateralFeeBreakdownGenerator->generate($irhpApplication)
        );
    }
}
