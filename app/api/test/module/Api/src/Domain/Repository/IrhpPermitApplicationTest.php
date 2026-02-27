<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use RuntimeException;

/**
 * IRHP Permit Application test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitApplicationTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitApplication::class);
    }

    public function testGetByIrhpApplicationWithStockInfo(): void
    {
        $irhpApplicationId = 7;

        $expectedResult = [
            [
                'irhpPermitApplication' => m::mock(IrhpPermitApplicationEntity::class),
                'validTo' => '2019-12-31',
                'countryId' => 'ES'
            ],
            [
                'irhpPermitApplication' => m::mock(IrhpPermitApplicationEntity::class),
                'validTo' => '2018-12-31',
                'countryId' => 'IT'
            ],
        ];

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with(
                'ipa as irhpPermitApplication, ips.validTo as validTo, ips.id as stockId, ' .
                'IDENTITY(ips.country) as countryId'
            )
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitApplicationEntity::class, 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipw.irhpPermitStock', 'ips')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipa.irhpApplication) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $irhpApplicationId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->once()
            ->andReturn($expectedResult);

        $this->assertEquals(
            $expectedResult,
            $this->sut->getByIrhpApplicationWithStockInfo($irhpApplicationId)
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestGetRequiredPermitCountWhereApplicationAwaitingPayment')]
    public function testGetRequiredPermitCountWhereApplicationAwaitingPayment(
        mixed $emissionsCategoryId,
        mixed $fieldName,
        mixed $queryReturnValue,
        mixed $expectedResult
    ): void {
        $stockId = 47;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('sum(ipa.' . $fieldName . ')')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitApplicationEntity::class, 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpPermitWindow', 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'ia')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ia.status = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $stockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, IrhpInterface::STATUS_AWAITING_FEE)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($queryReturnValue);

        $this->assertEquals(
            $expectedResult,
            $this->sut->getRequiredPermitCountWhereApplicationAwaitingPayment($stockId, $emissionsCategoryId)
        );
    }

    public static function dpTestGetRequiredPermitCountWhereApplicationAwaitingPayment(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, 'requiredEuro5', 33, 33],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, 'requiredEuro6', 33, 33],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, 'requiredEuro5', null, 0],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, 'requiredEuro6', null, 0],
        ];
    }

    public function testGetRequiredPermitCountWhereApplicationAwaitingPaymentBadEmissionsCategoryId(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Emissions category id bad_ref_data is not supported');

        $this->sut->getRequiredPermitCountWhereApplicationAwaitingPayment(47, 'bad_ref_data');
    }
}
