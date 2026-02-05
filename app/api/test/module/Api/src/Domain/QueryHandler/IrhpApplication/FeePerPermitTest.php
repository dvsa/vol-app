<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\FeePerPermit;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\FeePerPermit as FeePerPermitQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class FeePerPermitTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FeePerPermit();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQuerySupportedPermitType')]
    public function testHandleQuerySupportedPermitType(mixed $permitTypeId): void
    {
        $applicationFeeProdRef = 'APPLICATION_FEE_PROD_REF';

        $irhpPermitApplication2019Id = 7;
        $issueFee2019ProdRef = 'ISSUE_FEE_2019_PROD_REF';
        $feePerPermit2019 = 103;

        $irhpPermitApplication2020Id = 10;
        $issueFee2020ProdRef = 'ISSUE_FEE_2020_PROD_REF';
        $feePerPermit2020 = 133;

        $irhpPermitApplication2019 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2019->shouldReceive('getId')
            ->andReturn($irhpPermitApplication2019Id);
        $irhpPermitApplication2019->shouldReceive('getIssueFeeProductReference')
            ->andReturn($issueFee2019ProdRef);

        $irhpPermitApplication2020 = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication2020->shouldReceive('getId')
            ->andReturn($irhpPermitApplication2020Id);
        $irhpPermitApplication2020->shouldReceive('getIssueFeeProductReference')
            ->andReturn($issueFee2020ProdRef);

        $irhpPermitApplications = [$irhpPermitApplication2019, $irhpPermitApplication2020];

        $applicationFeeType = m::mock(FeeType::class);
        $issueFeeType2019 = m::mock(FeeType::class);
        $issueFeeType2020 = m::mock(FeeType::class);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($applicationFeeProdRef)
            ->andReturn($applicationFeeType);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($issueFee2019ProdRef)
            ->andReturn($issueFeeType2019);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($issueFee2020ProdRef)
            ->andReturn($issueFeeType2020);

        $irhpApplication = m::mock(IrhpApplication::class)->makePartial();
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($permitTypeId);
        $irhpApplication->shouldReceive('getApplicationFeeProductReference')
            ->andReturn($applicationFeeProdRef);
        $irhpApplication->shouldReceive('getFeePerPermit')
            ->with($applicationFeeType, $issueFeeType2019)
            ->andReturn($feePerPermit2019);
        $irhpApplication->shouldReceive('getFeePerPermit')
            ->with($applicationFeeType, $issueFeeType2020)
            ->andReturn($feePerPermit2020);
        $irhpApplication->shouldReceive('getIrhpPermitApplications')
            ->andReturn($irhpPermitApplications);

        $query = m::mock(FeePerPermitQry::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplication);

        $expectedResult = [
            7 => 103,
            10 => 133
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expectedResult, $result);
    }

    public static function dpTestHandleQuerySupportedPermitType(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_MULTILATERAL],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQueryUnsupportedPermitType')]
    public function testHandleQueryUnsupportedPermitType(mixed $permitTypeId): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('FeePerPermit query only supports bilateral and multilateral types');

        $irhpApplication = m::mock(IrhpApplication::class)->makePartial();
        $irhpApplication->shouldReceive('getIrhpPermitType->getId')
            ->andReturn($permitTypeId);

        $query = m::mock(FeePerPermitQry::class);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplication);

        $this->sut->handleQuery($query);
    }

    public static function dpTestHandleQueryUnsupportedPermitType(): array
    {
        return [
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT],
            [IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM],
        ];
    }
}
