<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Permits;

use Dvsa\Olcs\Api\Domain\QueryHandler\Permits\EcmtPermitFees;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepo;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class EcmtPermitFeesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EcmtPermitFees();
        $this->mockRepo('FeeType', FeeTypeRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $feeProductReference1 = 'IRHP_GV_APP_ECMT';
        $feeProductReference2 = 'IRHP_GV_ECMT_100_PERMIT_FEE';
        $feeType1 = m::mock(FeeType::class);
        $feeType2 = m::mock(FeeType::class);

        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($feeProductReference1)
            ->andReturn($feeType1);
        $this->repoMap['FeeType']->shouldReceive('getLatestByProductReference')
            ->with($feeProductReference2)
            ->andReturn($feeType2);

        $queryProductReferences = [$feeProductReference1, $feeProductReference2];

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getProductReferences')
            ->andReturn($queryProductReferences);

        $result = $this->sut->handleQuery($query);

        $this->assertTrue(isset($result['fee']));
        $this->assertCount(2, $result['fee']);

        $resultKeys = array_keys($result['fee']);
        $this->assertEquals($feeProductReference1, $resultKeys[0]);
        $this->assertEquals($feeProductReference2, $resultKeys[1]);

        $this->assertSame($feeType1, $result['fee'][$feeProductReference1]);
        $this->assertSame($feeType2, $result['fee'][$feeProductReference2]);
    }
}
