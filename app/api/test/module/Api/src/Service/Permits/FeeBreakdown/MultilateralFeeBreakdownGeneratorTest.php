<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\FeeBreakdown;

use DateTime;
use DateTimeZone;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Service\Permits\FeeBreakdown\MultilateralFeeBreakdownGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * MultilateralFeeBreakdownGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class MultilateralFeeBreakdownGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $applicationFeeType = m::mock(FeeType::class);

        $issueFeePartialProdRef = 'PRODUCT_REFERENCE_MULTILATERAL_ISSUE_PARTIAL';
        $issueFeeFullProdRef = 'PRODUCT_REFERENCE_MULTILATERAL_ISSUE_FULL';

        $issueFeeTypePartial = m::mock(FeeType::class);
        $issueFeeTypePartial->shouldReceive('getProductReference')
            ->andReturn($issueFeePartialProdRef);

        $issueFeeTypeFull = m::mock(FeeType::class);
        $issueFeeTypeFull->shouldReceive('getProductReference')
            ->andReturn($issueFeeFullProdRef);

        $outstandingIssueFee1 = m::mock(Fee::class);
        $outstandingIssueFee1->shouldReceive('getFeeType')
            ->andReturn($issueFeeTypePartial);
        $outstandingIssueFee1->shouldReceive('getInvoicedDate')
            ->andReturn(new DateTime('2019-06-01 13:14:26', new DateTimeZone('+0000')));

        $outstandingIssueFee2 = m::mock(Fee::class);
        $outstandingIssueFee2->shouldReceive('getFeeType')
            ->andReturn($issueFeeTypeFull);

        $outstandingIssueFees = [
            $outstandingIssueFee1,
            $outstandingIssueFee2,
        ];

        $irhpPermitApplication2019 = $this->createIrhpPermitApplicationMock(
            5,
            $this->createIrhpPermitStockMock(2019),
            $issueFeePartialProdRef
        );

        $irhpPermitApplication2020 = $this->createIrhpPermitApplicationMock(
            7,
            $this->createIrhpPermitStockMock(2020),
            $issueFeeFullProdRef
        );

        $irhpPermitApplication2021 = $this->createIrhpPermitApplicationMock(
            6,
            $this->createIrhpPermitStockMock(2021),
            $issueFeeFullProdRef
        );

        $irhpPermitApplication2022 = $this->createIrhpPermitApplicationMock(
            0,
            $this->createIrhpPermitStockMock(2022),
            $issueFeeFullProdRef
        );

        $irhpPermitApplications = [
            $irhpPermitApplication2019,
            $irhpPermitApplication2020,
            $irhpPermitApplication2021,
            $irhpPermitApplication2022,
        ];

        $partialFeePerPermit = 75;
        $fullFeePerPermit = 100;

        $irhpApplication = m::mock(IrhpApplication::class);
        $irhpApplication->shouldReceive('getIrhpPermitType->isMultilateral')
            ->andReturn(true);
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);
        $irhpApplication->shouldReceive('getLatestOutstandingApplicationFee->getFeeType')
            ->andReturn($applicationFeeType);
        $irhpApplication->shouldReceive('getOutstandingIssueFees')
            ->andReturn($outstandingIssueFees);
        $irhpApplication->shouldReceive('getFeePerPermit')
            ->with($applicationFeeType, $issueFeeTypePartial)
            ->andReturn($partialFeePerPermit);
        $irhpApplication->shouldReceive('getFeePerPermit')
            ->with($applicationFeeType, $issueFeeTypeFull)
            ->andReturn($fullFeePerPermit);

        $expectedResult = [
            [
                'year' => '2019',
                'validFromTimestamp' => 1559394866,
                'validToTimestamp' => 1577836799,
                'feePerPermit' => 75,
                'numberOfPermits' => 5,
                'totalFee' => 375,
            ],
            [
                'year' => '2020',
                'validFromTimestamp' => 1577836800,
                'validToTimestamp' => 1609459199,
                'feePerPermit' => 100,
                'numberOfPermits' => 7,
                'totalFee' => 700,
            ],
            [
                'year' => '2021',
                'validFromTimestamp' => 1609459200,
                'validToTimestamp' => 1640995199,
                'feePerPermit' => 100,
                'numberOfPermits' => 6,
                'totalFee' => 600,
            ],
        ];

        $multilateralFeeBreakdownGenerator = new MultilateralFeeBreakdownGenerator();

        $this->assertEquals(
            $expectedResult,
            $multilateralFeeBreakdownGenerator->generate($irhpApplication)
        );
    }

    private function createIrhpPermitApplicationMock(mixed $permitsRequired, mixed $irhpPermitStock, mixed $issueFeeProductReference): mixed
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('countPermitsRequired')
            ->andReturn($permitsRequired);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->andReturn($irhpPermitStock);
        $irhpPermitApplication->shouldReceive('getIssueFeeProductReference')
            ->andReturn($issueFeeProductReference);

        return $irhpPermitApplication;
    }

    private function createIrhpPermitStockMock(mixed $year): mixed
    {
        $dateTimeZone = new DateTimeZone('+0000');

        $irhpPermitStock = m::mock(IrhpPermitStock::class);
        $irhpPermitStock->shouldReceive('getValidFrom')
            ->andReturn(new DateTime($year . '-01-01 00:00:00', $dateTimeZone));
        $irhpPermitStock->shouldReceive('getValidTo')
            ->andReturn(new DateTime($year . '-12-31 23:59:59', $dateTimeZone));

        return $irhpPermitStock;
    }
}
