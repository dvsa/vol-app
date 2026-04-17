<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use DateTime;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as IrhpPermitWindowEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Mockery as m;
use Laminas\Db\Sql\Predicate\Between;

/**
 * IRHP Permit Window test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpPermitWindowTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(IrhpPermitWindow::class);
    }

    public function testFetchOpenWindows(): void
    {
        $expectedResult = [
            m::mock(IrhpPermitWindowEntity::class),
            m::mock(IrhpPermitWindowEntity::class),
        ];

        $dateTime = m::mock(DateTime::class);

        $irhpPermitStock = 1;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $andXFunc = m::mock(Func::class);
        $eqFunc = m::mock(Func::class);
        $betweenFunc = m::mock(Func::class);

        $expr = m::mock(Expr::class);

        $queryBuilder->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('andX')
            ->with($eqFunc, $betweenFunc)
            ->once()
            ->andReturn($andXFunc);

        $expr->shouldReceive('eq')
            ->with('?1', 'ipw.irhpPermitStock')
            ->once()
            ->andReturn($eqFunc)
            ->shouldReceive('between')
            ->with('?2', 'ipw.startDate', 'ipw.endDate')
            ->once()
            ->andReturn($betweenFunc);

        $queryBuilder->shouldReceive('select')
            ->with('ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitWindowEntity::class, 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with($andXFunc)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, $irhpPermitStock)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $dateTime)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->once()
            ->with(Query::HYDRATE_ARRAY)
            ->andReturn($expectedResult);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($queryBuilder)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $this->assertEquals(
            $expectedResult,
            $this->sut->fetchOpenWindows($irhpPermitStock, $dateTime)
        );
    }

    public function testFetchLastOpenWindowByStockId(): void
    {
        $expectedResult = m::mock(IrhpPermitWindowEntity::class);

        $irhpPermitStockId = 1;

        $queryBuilder = m::mock(QueryBuilder::class);
        $this->em->shouldReceive('createQueryBuilder')->once()->andReturn($queryBuilder);

        $betweenFunc = m::mock(Func::class);
        $eqFunc = m::mock(Func::class);
        $andXFunc = m::mock(Func::class);

        $expr = m::mock(Expr::class);

        $queryBuilder->shouldReceive('expr')
            ->andReturn($expr);

        $expr->shouldReceive('andX')
            ->with($betweenFunc, $eqFunc)
            ->once()
            ->andReturn($andXFunc);

        $expr->shouldReceive('between')
            ->with('?1', 'ipw.startDate', 'ipw.endDate')
            ->once()
            ->andReturn($betweenFunc)
            ->shouldReceive('eq')
            ->with('ipw.irhpPermitStock', '?2')
            ->once()
            ->andReturn($eqFunc);

        $queryBuilder->shouldReceive('select')
            ->with('ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('from')
            ->with(IrhpPermitWindowEntity::class, 'ipw')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('where')
            ->with($andXFunc)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('orderBy')
            ->with('ipw.id', 'DESC')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(1, m::type('DateTime'))
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with(2, $irhpPermitStockId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_ARRAY)
            ->once()
            ->andReturn([$expectedResult]);

        $this->assertEquals(
            $expectedResult,
            $this->sut->fetchLastOpenWindowByStockId($irhpPermitStockId, Query::HYDRATE_ARRAY)
        );
    }

    public function testFetchWindowsToBeClosed(): void
    {
        $now = new \DateTime('2018-10-25 13:21:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchWindowsToBeClosed($now, '-2 days'));

        $expectedQuery = 'BLAH '
            . 'AND ipw.endDate >= [[2018-10-23T00:00:00+00:00]] '
            . 'AND ipw.endDate < [[2018-10-25T13:21:10+00:00]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchOpenWindowsByCountry(): void
    {
        $now = new \DateTime('2018-10-25 13:21:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchOpenWindowsByCountry(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
                ['DE', 'NL'],
                $now
            )
        );

        $expectedQuery = 'BLAH '
            . 'SELECT ipw DISTINCT '
            . 'INNER JOIN ipw.irhpPermitStock ips '
            . 'INNER JOIN ips.irhpPermitType ipt '
            . 'INNER JOIN ips.country c '
            . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
            . 'AND ipw.startDate <= [[2018-10-25T13:21:10+00:00]] '
            . 'AND ipw.endDate > [[2018-10-25T13:21:10+00:00]] '
            . 'AND c.id IN [[["DE","NL"]]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchLastOpenWindowByIrhpPermitType(): void
    {
        $now = new \DateTime('2018-10-25 13:21:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(
            'RESULTS',
            $this->sut->fetchLastOpenWindowByIrhpPermitType(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                $now
            )
        );

        $expectedQuery = 'BLAH '
            . 'SELECT ipw '
            . 'INNER JOIN ipw.irhpPermitStock ips '
            . 'INNER JOIN ips.irhpPermitType ipt '
            . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT . ']] '
            . 'AND ipw.startDate <= [[2018-10-25T13:21:10+00:00]] '
            . 'AND ipw.endDate > [[2018-10-25T13:21:10+00:00]] '
            . 'ORDER BY ipw.endDate DESC '
            . 'LIMIT 1';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchLastOpenWindowByIrhpPermitTypeWhenNoWindowOpen(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);
        $this->expectExceptionMessage('No window available.');

        $now = new \DateTime('2018-10-25 13:21:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn([])
                ->getMock()
        );

        $this->sut->fetchLastOpenWindowByIrhpPermitType(
            IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
            $now
        );
    }

    public function testFetchLastOpenWindowByIrhpPermitTypeWithYear(): void
    {
        $now = new \DateTime('2018-10-25 13:21:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(
            'RESULTS',
            $this->sut->fetchLastOpenWindowByIrhpPermitType(
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
                $now,
                Query::HYDRATE_OBJECT,
                3030
            )
        );

        $expectedQuery = 'BLAH '
            . 'SELECT ipw '
            . 'INNER JOIN ipw.irhpPermitStock ips '
            . 'INNER JOIN ips.irhpPermitType ipt '
            . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT . ']] '
            . 'AND ipw.startDate <= [[2018-10-25T13:21:10+00:00]] '
            . 'AND ipw.endDate > [[2018-10-25T13:21:10+00:00]] '
            . 'ORDER BY ipw.endDate DESC '
            . 'LIMIT 1 '
            . 'AND ips.validTo BETWEEN [[3030-01-01T00:00:00+00:00]] AND [[3030-12-31T23:59:59+00:00]]';

        $this->assertEquals($expectedQuery, $this->query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchOpenWindowsByTypeProvider')]
    public function testFetchOpenWindowsByType(mixed $isInternal, mixed $expected): void
    {
        $now = new DateTime('2019-04-08 09:51:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchOpenWindowsByType(IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL, $now, $isInternal)
        );

        $this->assertEquals($expected, $this->query);
    }

    public static function fetchOpenWindowsByTypeProvider(): array
    {
        return [
            [false, 'BLAH '
                . 'SELECT ipw '
                . 'INNER JOIN ipw.irhpPermitStock ips '
                . 'INNER JOIN ips.irhpPermitType ipt '
                . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
                . 'AND ipw.startDate <= [[2019-04-08T09:51:10+00:00]] '
                . 'AND ipw.endDate > [[2019-04-08T09:51:10+00:00]] '
                . 'AND ips.hiddenSs != 1'],
            [true, 'BLAH '
                . 'SELECT ipw '
                . 'INNER JOIN ipw.irhpPermitStock ips '
                . 'INNER JOIN ips.irhpPermitType ipt '
                . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
                . 'AND ipw.startDate <= [[2019-04-08T09:51:10+00:00]] '
                . 'AND ipw.endDate > [[2019-04-08T09:51:10+00:00]]']
        ];
    }

    public function testFetchOpenWindowsByTypeYear(): void
    {
        $now = new DateTime('2019-04-08 09:51:10');

        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );

        $this->queryBuilder->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchOpenWindowsByTypeYear(IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT, $now, 3000)
        );

        $expectedQuery = 'BLAH '
            . 'SELECT ipw, ipr, ips '
            . 'INNER JOIN ipw.irhpPermitStock ips '
            . 'INNER JOIN ips.irhpPermitType ipt '
            . 'INNER JOIN ips.irhpPermitRanges ipr '
            . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT . ']] '
            . 'AND ipw.startDate <= [[2019-04-08T09:51:10+00:00]] '
            . 'AND ipw.endDate > [[2019-04-08T09:51:10+00:00]] '
            . 'AND ips.validTo BETWEEN [[3000-01-01T00:00:00+00:00]] AND [[3000-12-31T23:59:59+00:00]] '
            . 'AND ips.hiddenSs != 1';

        $this->assertEquals($expectedQuery, $this->query);
    }

    public static function fetchOpenWindowsByTypeYearProvider(): array
    {
        return [
            [false, 'BLAH '
                . 'SELECT ipw '
                . 'INNER JOIN ipw.irhpPermitStock ips '
                . 'INNER JOIN ips.irhpPermitType ipt '
                . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
                . 'AND ipw.startDate <= [[2019-04-08T09:51:10+00:00]] '
                . 'AND ipw.endDate > [[2019-04-08T09:51:10+00:00]] '
                . 'AND ips.hiddenSs != 1'],
            [true, 'BLAH '
                . 'SELECT ipw '
                . 'INNER JOIN ipw.irhpPermitStock ips '
                . 'INNER JOIN ips.irhpPermitType ipt '
                . 'AND ipt.id = [[' . IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL . ']] '
                . 'AND ipw.startDate <= [[2019-04-08T09:51:10+00:00]] '
                . 'AND ipw.endDate > [[2019-04-08T09:51:10+00:00]]']
        ];
    }

    public function testFindOverlappingWindowsByType(): void
    {
        $mockQb = m::mock(\Doctrine\ORM\QueryBuilder::class);
        $this->em->shouldReceive('getRepository->createQueryBuilder')->once()->andReturn($mockQb);
        $mockWindow = m::mock(IrhpPermitWindowEntity::class);

        $betweenFn = m::mock(Between::class);

        $mockQb->shouldReceive('expr->between')
            ->once()
            ->with('ipw.startDate', ':proposedStartDate', ':proposedEndDate')
            ->andReturn($betweenFn);

        $mockQb->shouldReceive('expr->between')
            ->once()
            ->with('ipw.endDate', ':proposedStartDate', ':proposedEndDate')
            ->andReturn($betweenFn);

        $mockQb->shouldReceive('expr->between')
            ->once()
            ->with(':proposedStartDate', 'ipw.startDate', 'ipw.endDate')
            ->andReturn($betweenFn);

        $mockQb->shouldReceive('expr->eq')
            ->once()
            ->with('ipw.irhpPermitStock', ':irhpPermitStock')
            ->andReturn('eqcond');

        $mockQb->shouldReceive('expr->neq')
            ->once()
            ->with('ipw.id', ':irhpPermitWindow')
            ->andReturn('neqcond');

        $mockQb->shouldReceive('orWhere')
            ->once()
            ->with($betweenFn)
            ->andReturnSelf();

        $mockQb->shouldReceive('orWhere')
            ->once()
            ->with($betweenFn)
            ->andReturnSelf();

        $mockQb->shouldReceive('orWhere')
            ->once()
            ->with($betweenFn)
            ->andReturnSelf();

        $mockQb->shouldReceive('andWhere')
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->times(4)
            ->andReturnSelf()
            ->shouldReceive('getQuery->getResult')
            ->withNoArgs()
            ->once()
            ->andReturn(['RESULTS']);

        $this->assertEquals(
            ['RESULTS'],
            $this->sut->findOverlappingWindowsByType(11, '2029-01-01 11:11:11', '2029-01-02 12:12:12', $mockWindow)
        );
    }
}
