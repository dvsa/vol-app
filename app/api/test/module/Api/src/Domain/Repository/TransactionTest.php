<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Transaction as TransactionRepo;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Fee\Transaction;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\Repository\Transaction
 */
class TransactionTest extends RepositoryTestCase
{
    /** @var  TransactionRepo */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(TransactionRepo::class);
    }

    public function testFetchByReference(): void
    {
        $ref = 'OLCS-1234-ABCD';

        $result = m::mock(Transaction::class);
        $results = [$result];

        /** @var m\MockInterface $qb */
        $qb = m::mock(QueryBuilder::class);
        $qb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn($results);

        $where = m::mock();
        $qb->shouldReceive('expr->eq')
            ->with('t.reference', ':reference')
            ->andReturn($where);
        $qb
            ->shouldReceive('andWhere')
            ->with($where)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('reference', $ref)
            ->once()
            ->andReturnSelf();

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($qb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('with')
            ->andReturnSelf();

        /** @var m\MockInterface $repo */
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('t')
            ->once()
            ->andReturn($qb);

        $this->em->shouldReceive('getRepository')
            ->with(Transaction::class)
            ->andReturn($repo)
            ->shouldReceive('lock')
            ->with($result, LockMode::OPTIMISTIC, 1);

        $this->sut->fetchByReference($ref, Query::HYDRATE_OBJECT, 1);
    }

    public function testfetchOutstandingCardPayments(): void
    {
        $mockQb = $this->createMockQb('{QUERY}');

        $this->mockCreateQueryBuilder($mockQb);

        $this->em->shouldReceive('getReference')
            ->andReturnUsing(
                function ($refData, $input) {
                    unset($refData); // unused
                    return $input;
                }
            );

        $mockQb->shouldReceive('getQuery->getResult')
            ->once()
            ->andReturn(['RESULTS']);

        $now = new DateTime();
        $expectedDateTime = $now->sub(new \DateInterval('PT60M'))->format(\DateTime::W3C);
        $expectedQry = '{QUERY}'
            . ' AND t.type = [[trt_payment]]'
            . ' AND t.status = [[pay_s_os]]'
            . ' AND t.paymentMethod IN [[["fpm_card_online","fpm_card_offline"]]]'
            . ' AND t.createdOn < [[' . $expectedDateTime . ']]';

        $this->assertEquals(['RESULTS'], $this->sut->fetchOutstandingCardPayments(60));

        $this->assertEquals($expectedQry, $this->query);
    }
}
