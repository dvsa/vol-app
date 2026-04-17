<?php

declare(strict_types=1);

/**
 * IrfoPermitStock Repo test
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPermitStock as Repo;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * IrfoPermitStock Repo test
 */
class IrfoPermitStockTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchUsingSerialNoStartEnd(): void
    {
        $irfoCountryId = 99;
        $validForYear = 2015;
        $serialNoStart = 1;
        $serialNoEnd = 2;

        $mockResult = [0 => 'result'];

        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getIrfoCountry')
            ->once()
            ->andReturn($irfoCountryId);
        $command->shouldReceive('getValidForYear')
            ->once()
            ->andReturn($validForYear);
        $command->shouldReceive('getSerialNoStart')
            ->once()
            ->andReturn($serialNoStart);
        $command->shouldReceive('getSerialNoEnd')
            ->once()
            ->andReturn($serialNoEnd);

        /** @var Expr $expr */
        $expr = m::mock(QueryBuilder::class);
        $expr->shouldReceive('eq')
            ->once()
            ->with(m::type('string'), ':byIrfoCountry')
            ->andReturnSelf();
        $expr->shouldReceive('eq')
            ->once()
            ->with(m::type('string'), ':byValidForYear')
            ->andReturnSelf();
        $expr->shouldReceive('gte')
            ->once()
            ->with(m::type('string'), ':bySerialNoStart')
            ->andReturnSelf();
        $expr->shouldReceive('lte')
            ->once()
            ->with(m::type('string'), ':bySerialNoEnd')
            ->andReturnSelf();

        /** @var QueryBuilder $qb */
        $qb = m::mock(QueryBuilder::class);

        $qb->shouldReceive('expr')
            ->andReturn($expr);

        $qb->shouldReceive('setParameter')
            ->once()
            ->with('byIrfoCountry', $irfoCountryId)
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->once()
            ->with('byValidForYear', $validForYear)
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->once()
            ->with('bySerialNoStart', $serialNoStart)
            ->andReturnSelf();
        $qb->shouldReceive('setParameter')
            ->once()
            ->with('bySerialNoEnd', $serialNoEnd)
            ->andReturnSelf();

        $qb->shouldReceive('andWhere')
            ->with($expr)
            ->andReturnSelf();
        $qb->shouldReceive('indexBy')
            ->once()
            ->with(m::type('string'), 'm.serialNo')
            ->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($mockResult);

        /** @var EntityRepository $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(IrfoPermitStock::class)
            ->andReturn($repo);

        $result = $this->sut->fetchUsingSerialNoStartEnd($command);

        $this->assertEquals($mockResult, $result);
    }
}
