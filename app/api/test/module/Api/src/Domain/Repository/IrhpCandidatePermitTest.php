<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit as IrhpCandidatePermitEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetList;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplication;
use Dvsa\Olcs\Transfer\Query\IrhpCandidatePermit\GetListByIrhpApplicationUnpaged;
use Mockery as m;

/**
 * IRHP Candidate Permit test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpCandidatePermitTest extends RepositoryTestCase
{
    public const IRHP_APPLICATION_ID = 10;

    public function setUp(): void
    {
        $this->setUpSut(IrhpCandidatePermit::class);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetListByIrhpApplicationVariants')]
    public function testFetchListForGetListByIrhpApplication(mixed $query, mixed $paginateCallCount): void
    {
        $this->setUpSut(IrhpCandidatePermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('with')->with('ipa.irhpApplication', 'ia')->once()->andReturnSelf()
            ->shouldReceive('paginate')->times($paginateCallCount)->andReturnSelf()
            ->shouldReceive('order')->once()->andReturnSelf();

        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.successful = [[true]] '
            . 'AND ia.status = [[' . RefData::PERMIT_APP_STATUS_AWAITING_FEE . ']] '
            . 'AND ipa.irhpApplication = [[' . self::IRHP_APPLICATION_ID . ']]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpGetListByIrhpApplicationVariants(): array
    {
        $params = [
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'page' => 1,
            'limit' => 25,
            'order' => 'id',
            'sort' => 'ASC',
        ];

        return [
            [GetListByIrhpApplication::create($params), 1],
            [GetListByIrhpApplicationUnpaged::create($params), 0],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetListByIrhpApplicationWantedOnlyVariants')]
    public function testFetchListForGetListByIrhpApplicationWantedOnly(mixed $query, mixed $paginateCallCount): void
    {
        $this->setUpSut(IrhpCandidatePermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('with')->with('ipa.irhpApplication', 'ia')->once()->andReturnSelf()
            ->shouldReceive('paginate')->times($paginateCallCount)->andReturnSelf()
            ->shouldReceive('order')->once()->andReturnSelf();

        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND m.successful = [[true]] '
            . 'AND ia.status = [[' . RefData::PERMIT_APP_STATUS_AWAITING_FEE . ']] '
            . 'AND ipa.irhpApplication = [[' . self::IRHP_APPLICATION_ID . ']] '
            . 'AND m.wanted = [[true]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpGetListByIrhpApplicationWantedOnlyVariants(): array
    {
        $params = [
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'page' => 1,
            'limit' => 25,
            'order' => 'id',
            'sort' => 'ASC',
            'wantedOnly' => true,
        ];

        return [
            [GetListByIrhpApplication::create($params), 1],
            [GetListByIrhpApplicationUnpaged::create($params), 0],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetListByIrhpApplicationPreGrantVariants')]
    public function testFetchListForGetListByIrhpApplicationPreGrant(mixed $query, mixed $paginateCallCount): void
    {
        $this->setUpSut(IrhpCandidatePermit::class, true);
        $this->sut->shouldReceive('fetchPaginatedList')->andReturn(['RESULTS']);

        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('irhpPermitApplication', 'ipa')->once()->andReturnSelf()
            ->shouldReceive('with')->with('ipa.irhpApplication', 'ia')->once()->andReturnSelf()
            ->shouldReceive('paginate')->times($paginateCallCount)->andReturnSelf()
            ->shouldReceive('order')->once()->andReturnSelf();

        $this->assertEquals(['RESULTS'], $this->sut->fetchList($query));

        $expectedQuery = 'BLAH '
            . 'AND ia.status IN [[["' . RefData::PERMIT_APP_STATUS_UNDER_CONSIDERATION . '"]]] '
            . 'AND ipa.irhpApplication = [[' . self::IRHP_APPLICATION_ID . ']]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function dpGetListByIrhpApplicationPreGrantVariants(): array
    {
        $params = [
            'irhpApplication' => self::IRHP_APPLICATION_ID,
            'page' => 1,
            'limit' => 25,
            'order' => 'id',
            'sort' => 'ASC',
            'isPreGrant' => true
        ];

        return [
            [GetListByIrhpApplication::create($params), 1],
            [GetListByIrhpApplicationUnpaged::create($params), 0],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFetchCountInRangeWhereApplicationAwaitingFee')]
    public function testFetchCountInRangeWhereApplicationAwaitingFee(mixed $countInRange, mixed $expectedResult): void
    {
        $rangeId = 22;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(icp.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'ia')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(icp.irhpPermitRange) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ia.status = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $rangeId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, IrhpInterface::STATUS_AWAITING_FEE)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($countInRange);

        $this->assertEquals(
            $expectedResult,
            $this->sut->fetchCountInRangeWhereApplicationAwaitingFee($rangeId)
        );
    }

    public static function dpFetchCountInRangeWhereApplicationAwaitingFee(): array
    {
        return [
            [null, 0],
            [42, 42]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpFetchCountInStockWhereApplicationAwaitingFee')]
    public function testFetchCountInStockWhereApplicationAwaitingFee(mixed $emissionsCategoryId, mixed $countInStock, mixed $expectedResult): void
    {
        $stockId = 22;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $queryBuilder->shouldReceive('select')
            ->with('count(icp.id)')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpCandidatePermitEntity::class, 'icp')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitApplication', 'ipa')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('icp.irhpPermitRange', 'ipr')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('innerJoin')
            ->with('ipa.irhpApplication', 'ia')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with('IDENTITY(ipr.irhpPermitStock) = ?1')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('ia.status = ?2')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('IDENTITY(ipr.emissionsCategory) = ?3')
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
            ->shouldReceive('setParameter')
            ->with(3, $emissionsCategoryId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getSingleScalarResult')
            ->once()
            ->andReturn($countInStock);

        $this->assertEquals(
            $expectedResult,
            $this->sut->fetchCountInStockWhereApplicationAwaitingFee($stockId, $emissionsCategoryId)
        );
    }

    public static function dpFetchCountInStockWhereApplicationAwaitingFee(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, null, 0],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, null, 0],
            [null, null, 0],
            [RefData::EMISSIONS_CATEGORY_EURO5_REF, 20, 20],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF, 20, 20],
            [null, 20, 20]
        ];
    }
}
