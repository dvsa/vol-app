<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Fee;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Transfer\Query\Fee\FeeList as FeeListQry;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Doctrine\ORM\Query;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\Fee::class)]
class FeeTest extends RepositoryTestCase
{
    /** @var   FeeRepo */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(FeeRepo::class, true);
    }

    private function setupFetchInterimFeesByApplicationId(m\MockInterface $mockQb, mixed $applicationId): void
    {
        $this->em->shouldReceive('getRepository->createQueryBuilder')->with('f')->once()->andReturn($mockQb);
        $this->queryBuilder->shouldReceive('withRefdata')->with()->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('order')->with('invoicedDate', 'ASC')->once()->andReturnSelf();

        $mockQb->shouldReceive('join')->with('f.feeType', 'ft')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('ft.feeType', ':feeTypeFeeType')->once()->andReturn('foo');
        $mockQb->shouldReceive('andWhere')->with('foo')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('f.application', ':applicationId')->once()->andReturn('bar');
        $mockQb->shouldReceive('andWhere')->with('bar')->once()->andReturnSelf();

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            FeeTypeEntity::FEE_TYPE_GRANTINT
        )->once()->andReturn('refdata');
        $mockQb->shouldReceive('setParameter')->with('feeTypeFeeType', 'refdata')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('applicationId', $applicationId)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');
    }

    public function testFetchInterimFeesByApplicationId(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->setupFetchInterimFeesByApplicationId($mockQb, 33);

        $this->assertSame('result', $this->sut->fetchInterimFeesByApplicationId(33));
    }

    public function testFetchInterimFeesByApplicationIdOutstanding(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->setupFetchInterimFeesByApplicationId($mockQb, 12);

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            FeeEntity::STATUS_OUTSTANDING
        )->once()->andReturn('ot');

        $mockQb->shouldReceive('expr->eq')->with('f.feeStatus', ':feeStatus')->once()->andReturn('expr-eq');
        $mockQb->shouldReceive('andWhere')->with('expr-eq')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('feeStatus', 'ot')->once();

        $this->assertSame('result', $this->sut->fetchInterimFeesByApplicationId(12, true));
    }

    public function testFetchInterimRefunds(): void
    {
        $alias = 'f';
        $startDate = new DateTime();
        $startDate = $startDate->sub(new \DateInterval('P' . abs((7 - date("N") - 7)) . 'D'));
        $endDate = new DateTime();
        $trafficAreas = ['B', 'C'];
        $sort = 'invoicedDate';
        $order = 'DESC';

        $mockRepo = m::mock(Fee::class);
        $mockRepo->shouldAllowMockingProtectedMethods();

        $this->em->shouldReceive('getRepository')->andReturn($mockRepo);

        $mockQb = m::mock(QueryBuilder::class);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('order')->with($sort, $order)->once()->andReturnSelf();

        $mockQb->shouldReceive('leftJoin')->with($alias . '.application', 'a')->once()->andReturnSelf();
        $mockQb->shouldReceive('join')->with($alias . '.feeType', 'fty')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('fty.feeType', ':feeType')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->isNotNull')->with('COALESCE(a.withdrawnDate, a.refusedDate, a.grantedDate)')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->gte')->with($alias . '.invoicedDate', ':after')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->lte')->with($alias . '.invoicedDate', ':before')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->in')->once()->with('f.feeStatus', ':feeStatus')->andReturnSelf();
        $mockQb->shouldReceive('expr->in')->once()->with('l.trafficArea', ':trafficArea')->andReturnSelf();
        $mockQb->shouldReceive('expr')->andReturn(new Expr());

        $mockQb->shouldReceive('andWhere')->andReturnSelf();

        $feeStatuses = [
            FeeEntity::STATUS_REFUNDED,
            FeeEntity::STATUS_REFUND_FAILED,
            FeeEntity::STATUS_REFUND_PENDING
        ];
        $mockQb->shouldReceive('setParameter')->with('feeStatus', $feeStatuses)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('feeType', FeeTypeEntity::FEE_TYPE_GRANTINT)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('after', $startDate)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('before', $endDate)->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('trafficArea', $trafficAreas)->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $mockRepo->shouldReceive('createQuerybuilder')->andReturn($mockQb);
        $mockRepo->shouldReceive('getQueryBuilder')->andReturn($mockQb);

        $this->assertSame('result', $this->sut->fetchInterimRefunds($startDate, $endDate, $sort, $order, $trafficAreas));
    }

    public function testFetchInterimFeesByApplicationIdPaid(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->setupFetchInterimFeesByApplicationId($mockQb, 12);

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            FeeEntity::STATUS_PAID
        )->once()->andReturn('ot');

        $mockQb->shouldReceive('expr->eq')->with('f.feeStatus', ':feeStatus')->once()->andReturn('expr-eq');
        $mockQb->shouldReceive('andWhere')->with('expr-eq')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('feeStatus', 'ot')->once();

        $this->assertSame('result', $this->sut->fetchInterimFeesByApplicationId(12, false, true));
    }

    public function testFetchInterimFeesByApplicationIdOutstandingOrPaid(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->setupFetchInterimFeesByApplicationId($mockQb, 12);

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            FeeEntity::STATUS_PAID
        )->once()->andReturn('ot');

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            FeeEntity::STATUS_OUTSTANDING
        )->once()->andReturn('pd');

        $mockQb->shouldReceive('expr->in')->with('f.feeStatus', ':feeStatus')->once()->andReturn('expr-in');
        $mockQb->shouldReceive('andWhere')->with('expr-in')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('feeStatus', ['ot', 'pd'])->once();

        $this->assertSame('result', $this->sut->fetchInterimFeesByApplicationId(12, true, true));
    }

    public function testFetchOutstandingFeesByOrganisationId(): void
    {
        $organisationId = 123;

        $mockQb = m::mock(QueryBuilder::class);

        $ceasedStatuses = [
            LicenceEntity::LICENCE_STATUS_CONTINUATION_NOT_SOUGHT,
            LicenceEntity::LICENCE_STATUS_REVOKED,
            LicenceEntity::LICENCE_STATUS_SURRENDERED,
            LicenceEntity::LICENCE_STATUS_TERMINATED
        ];

        $mockQb->shouldReceive('expr->notIn')->with('l.status', ':ceasedStatuses')->once()->andReturn('condition1');
        $mockQb->shouldReceive('expr->neq')->with('ftype.feeType', ':feeType')->once()->andReturn('condition2');
        $mockQb->shouldReceive('andWhere')->with('condition1')->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('ceasedStatuses', $ceasedStatuses)->andReturnSelf();
        $mockQb->shouldReceive('innerJoin')->with('f.feeType', 'ftype')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->with('condition2')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('feeType', RefDataEntity::FEE_TYPE_CONT)->once()->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('order')->with('invoicedDate', 'ASC')->once()->andReturnSelf();

        $this->mockWhereOutstandingFee($mockQb);

        $this->mockWhereCurrentLicenceOrApplicationFee($mockQb, $organisationId);

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchOutstandingFeesByOrganisationId($organisationId, true, true)
        );
    }

    public function testFetchFeesByIrfoGvPermitId(): void
    {
        $irfoGvPermitId = 123;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('order')
            ->with('invoicedDate', 'ASC')
            ->once()
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('irfoGvPermitId', $irfoGvPermitId)
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchFeesByIrfoGvPermitId($irfoGvPermitId)
        );
    }

    public function testFetchOutstandingFeesByIds(): void
    {
        $ids = [1, 2, 3];

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('order')->with('invoicedDate', 'ASC')->once()->andReturnSelf();

        $this->mockWhereOutstandingFee($mockQb);

        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('expr->in');
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('feeIds', $ids)
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchOutstandingFeesByIds($ids)
        );
    }

    /**
     * @param string $status
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testFetchList(mixed $status): void
    {
        // in practice this query would never return results, but it covers all
        // possible conditions
        $query = FeeListQry::create(
            [
                'application' => 11,
                'licence' => 12,
                'task' => 13,
                'busReg' => 14,
                'irfoGvPermit' => 15,
                'organisation' => 16,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
                'isMiscellaneous' => true,
                'ids' => [1, 2, 3],
                'status' => $status,
            ]
        );

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('paginate')
            ->once()
            ->with(1, 10)
            ->andReturnSelf()
            ->shouldReceive('order')
            ->once()
            ->with('id', 'ASC', [])
            ->andReturnSelf()
            ->shouldReceive('withCreatedBy')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('filterByLicence')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('filterByApplication')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('filterByIds')
            ->once()
            ->andReturnSelf();

        $busRegQb = m::mock(QueryBuilder::class);
        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('br')
            ->once()
            ->andReturn($busRegQb);

        $busRegQb
            ->shouldReceive('select')->once()->andReturnSelf()
            ->shouldReceive('join')->once()->andReturnSelf()
            ->shouldReceive('where')->once()->andReturnSelf()
            ->shouldReceive('andWhere')->once()->andReturnSelf()
            ->shouldReceive('setParameter')->once()->with('id', 14)->andReturnSelf();

        $busRegIds = [14, 15, 16];
        $busRegQb
            ->shouldReceive('getQuery->getArrayResult')
            ->once()
            ->andReturn($busRegIds);

        // we *could* assert all the conditions here, but just stub the methods for now
        $mockQb
            ->shouldReceive('andWhere')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('setParameter')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('innerJoin')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->times(2)->andReturnSelf();

        $mockQb->shouldReceive('expr->orX')->times(2);
        $mockQb->shouldReceive('expr->eq')->times(2);
        $mockQb->shouldReceive('expr->isNotNull')->times(2);
        $mockQb->shouldReceive('expr->in');

        // mock pagination
        $mockQuery = m::mock();
        $mockQb->shouldReceive('getQuery')->andReturn($mockQuery);
        $mockQuery->shouldReceive('setHydrationMode');
        $paginator = m::mock();
        $this->sut->shouldReceive('getPaginator')->andReturn($paginator);
        $paginator->shouldReceive('getIterator')->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchList($query)
        );
    }

    public static function statusProvider(): array
    {
        return [
            ['all'],
            ['current'],
            ['historical'],
        ];
    }

    public function testFetchLatestFeeByTypeStatusesAndApplicationId(): void
    {
        $feeType = 'APP';
        $feeStatuses = ['lfs_ot', 'lfs_cn'];
        $applicationId = 69;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('order')->with('invoicedDate', 'DESC')->once()->andReturnSelf();

        $mockQb
            ->shouldReceive('expr->in');
        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('andWhere')->zeroOrMoreTimes()->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('application', $applicationId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('feeType', $feeType)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('feeStatuses', $feeStatuses)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setMaxResults')
            ->with(1)
            ->andReturnSelf();

        $fee1 = m::mock();
        $results = [$fee1];
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn($results);

        $this->assertSame(
            $fee1,
            $this->sut->fetchLatestFeeByTypeStatusesAndApplicationId($feeType, $feeStatuses, $applicationId)
        );
    }

    public function testFetchLatestFeeByTypeStatusesAndApplicationIdNull(): void
    {
        $feeType = 'APP';
        $feeStatuses = ['lfs_ot', 'lfs_cn'];
        $appId = 69;

        $mockQb = m::mock(QueryBuilder::class);

        $mockExpr = m::mock(Expr::class)
            ->shouldReceive('eq')->withAnyArgs()->andReturnSelf()
            ->shouldReceive('in')->withAnyArgs()->andReturnSelf()
            ->getMock();
        $mockQb->shouldReceive('expr')->with()->andReturn($mockExpr);

        $mockQb
            ->shouldReceive('expr')->with()->andReturn($mockQb)
            ->shouldReceive('andWhere')->with($mockExpr)->times(3)->andReturnSelf()
            ->shouldReceive('setParameter')->times(3)->andReturnSelf()
            ->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf()
            ->shouldReceive('getQuery->getResult')->with()->once()->andReturn([]);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')->with('f')->once()->andReturn($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('order')->once()->andReturnSelf();

        static::assertNull(
            $this->sut->fetchLatestFeeByTypeStatusesAndApplicationId($feeType, $feeStatuses, $appId)
        );
    }

    public function testFetchOutstandingFeesByApplicationId(): void
    {
        $applicationId = 69;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->mockWhereOutstandingFee($mockQb);

        $mockQb
            ->shouldReceive('expr->eq')
            ->with('f.application', ':application')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('application', $applicationId)
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchOutstandingFeesByApplicationId($applicationId)
        );
    }

    private function mockWhereOutstandingFee(m\MockInterface $mockQb): void
    {
        $where = m::mock();
        $mockQb
            ->shouldReceive('expr->eq')
            ->with('f.feeStatus', ':feeStatus')
            ->once()
            ->andReturn($where);

        $mockQb
            ->shouldReceive('setParameter')
            ->with('feeStatus', m::any()) // refdata 'lfs_ot'
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('andWhere')
            ->with($where)
            ->andReturnSelf();

        $this->em
            ->shouldReceive('getReference');
    }

    private function mockWhereCurrentLicenceOrApplicationFee(m\MockInterface $mockQb, mixed $organisationId): void
    {
        $mockQb
            ->shouldReceive('leftJoin')
            ->with('f.application', 'a')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('leftJoin')
            ->with('f.licence', 'l')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('leftJoin')
            ->with('a.licence', 'al')
            ->once()
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('expr->in');
        $mockQb
            ->shouldReceive('expr->orX');
        $mockQb
            ->shouldReceive('expr->isNotNull');
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('setParameter')
            ->with('organisationId', $organisationId)
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('appStatus', m::type('array')) // refdata ['apsts_consideration', 'apsts_granted']
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('licStatus', m::type('array')); // refdata ['lsts_valid', 'lsts_curtailed', 'lsts_suspended']

        $this->em
            ->shouldReceive('getReference');
    }

    public function testFetchOutstandingGrantFeesByApplicationId(): void
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
            ->andReturn('Foo');

        $this->assertEquals('Foo', $this->sut->fetchOutstandingGrantFeesByApplicationId(111));

        $this->assertEquals(
            '{QUERY}'
            // whereOutstandingFee
            . ' AND f.feeStatus = [[lfs_ot]]'
            . ' INNER JOIN f.feeType ft AND f.application = [[111]] AND ft.feeType = [[GRANT]]',
            $this->query
        );
    }

    public function testFetchOutstandingContinuationFeesByLicenceId(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            FeeEntity::STATUS_OUTSTANDING
        )->once()->andReturn('ot');

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()
                ->shouldReceive('execute')->zeroOrMoreTimes()->andReturnNull()
                ->shouldReceive('getResult')->andReturn(['RESULTS'])->getMock()
        );

        $after = new \DateTime('2015-09-22');
        $expectedAfterStr = $after->format(\DateTime::W3C);
        $this->assertEquals(
            ['RESULTS'],
            $this->sut->fetchOutstandingContinuationFeesByLicenceId(716, $after)
        );

        $expectedQuery = 'BLAH INNER JOIN f.feeType ft AND f.licence = [[716]] AND '
            . 'ft.feeType = [[CONT]] AND f.feeStatus = [[ot]]'
            . ' AND f.invoicedDate >= [[' . $expectedAfterStr . ']]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchLatestPaidFeeByApplicationId(): void
    {
        $applicationId = 69;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $mockQb
            ->shouldReceive('expr->eq')->with('f.application', ':application')->once()->andReturn('cond1');

        $mockQb
            ->shouldReceive('innerJoin')->with('f.feeTransactions', 'ft')->once()->andReturnSelf()
            ->shouldReceive('innerJoin')->with('ft.transaction', 't')->once()->andReturnSelf()
            ->shouldReceive('addOrderBy')->with('t.completedDate', 'DESC')->once()->andReturnSelf()
            ->shouldReceive('addOrderBy')->with('t.id', 'DESC')->once()->andReturnSelf()
            ->shouldReceive('andWhere')->with('cond1')->andReturnSelf()
            ->shouldReceive('setParameter')->with('application', $applicationId)->once()->andReturnSelf()
            ->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn(['result']);

        $this->assertSame('result', $this->sut->fetchLatestPaidFeeByApplicationId($applicationId));
    }

    public function testFetchFeesByPsvAuthIdAndType(): void
    {
        $irfoPsvAuthId = 123;
        $feeTypeFeeType = 'fee-type-fee-type';

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->em->shouldReceive('getReference')->with(
            RefDataEntity::class,
            $feeTypeFeeType
        )->once()->andReturn($feeTypeFeeType);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('order')
            ->with('invoicedDate', 'DESC')
            ->once()
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('join')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('irfoPsvAuthId', $irfoPsvAuthId)
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('feeTypeFeeType', m::type('string'))
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchFeesByPsvAuthIdAndType($irfoPsvAuthId, $feeTypeFeeType)
        );
    }

    public function testFetchApplicationFeeByPsvAuthId(): void
    {
        $irfoPsvAuthId = 123;

        $this->sut->shouldReceive('fetchFeesByPsvAuthIdAndType')
            ->with($irfoPsvAuthId, FeeTypeEntity::FEE_TYPE_IRFOPSVAPP)
            ->andReturn(['foo']);

        $this->assertStringContainsString(
            'foo',
            $this->sut->fetchApplicationFeeByPsvAuthId($irfoPsvAuthId)
        );
    }

    public function testFetchApplicationFeeByPsvAuthIdNoFees(): void
    {
        $irfoPsvAuthId = 123;

        $this->sut->shouldReceive('fetchFeesByPsvAuthIdAndType')
            ->with($irfoPsvAuthId, FeeTypeEntity::FEE_TYPE_IRFOPSVAPP)
            ->andReturn([]);

        $this->assertEmpty($this->sut->fetchApplicationFeeByPsvAuthId($irfoPsvAuthId));
    }

    public function testFetchFeesByIrfoPsvAuthId(): void
    {
        $irfoPsvAuthId = 123;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('order')
            ->with('invoicedDate', 'ASC')
            ->once()
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('irfoPsvAuthId', $irfoPsvAuthId)
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchFeesByIrfoPsvAuthId($irfoPsvAuthId)
        );
    }

    public function testFetchFeesByIrfoPsvAuthIdOutstanding(): void
    {
        $irfoPsvAuthId = 123;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getReference')
            ->with(
                RefDataEntity::class,
                FeeEntity::STATUS_OUTSTANDING
            )
            ->once()
            ->andReturn('ot')
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)
            ->andReturnSelf()
            ->shouldReceive('withRefdata')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('order')
            ->with('invoicedDate', 'ASC')
            ->once()
            ->andReturnSelf();

        $mockQb
            ->shouldReceive('expr->eq');
        $mockQb
            ->shouldReceive('andWhere')
            ->andReturnSelf();
        $mockQb
            ->shouldReceive('setParameter')
            ->with('irfoPsvAuthId', $irfoPsvAuthId)
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('feeStatus', 'ot')
            ->andReturnSelf();

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame(
            'result',
            $this->sut->fetchFeesByIrfoPsvAuthId($irfoPsvAuthId, true)
        );
    }

    public function testFetchFeeByTypeAndApplicationId(): void
    {
        $feeType = 'APP';
        $applicationId = 69;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class)
            ->shouldReceive('expr')
            ->andReturn(
                m::mock()
                    ->shouldReceive('eq')
                    ->with('f.application', ':application')
                    ->andReturn('FOO')
                    ->once()
                    ->shouldReceive('eq')
                    ->with('ft.feeType', ':feeType')
                    ->andReturn('BAR')
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->shouldReceive('andWhere')
            ->with('FOO')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('andWhere')
            ->with('BAR')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('application', $applicationId)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setParameter')
            ->with('feeType', $feeType)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('join')
            ->with('f.feeType', 'ft')
            ->once()
            ->andReturnSelf()
            ->getMock();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $results = [m::mock()];

        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn($results);

        $this->assertSame(
            $results,
            $this->sut->fetchFeeByTypeAndApplicationId($feeType, $applicationId)
        );
    }

    public function testFetchFeesByIds(): void
    {
        $ids = [1, 2, 3];

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->once()->with($mockQb)->andReturnSelf()
            ->shouldReceive('withRefdata')->once()->andReturnSelf()
            ->shouldReceive('with')->with('licence')->once()->andReturnSelf()
            ->shouldReceive('with')->with('application')->once()->andReturnSelf()
            ->shouldReceive('with')->with('feeTransactions', 'ft')->once()->andReturnSelf()
            ->shouldReceive('with')->with('ft.transaction', 't')->once()->andReturnSelf()
            ->shouldReceive('with')->with('t.status')->once()->andReturnSelf()
            ->shouldReceive('order')->with('invoicedDate', 'ASC')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('f.id', ':feeIds')->once()->andReturn('IN');
        $mockQb->shouldReceive('andWhere')->with('IN')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('feeIds', $ids)->andReturnSelf();
        $mockQb->shouldReceive('getQuery->getResult')->once()->andReturn('result');

        $this->assertSame('result', $this->sut->fetchFeesByIds($ids));
    }

    public function testFetchLatestPaidContinuationFee(): void
    {
        $licenceId = 1;

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('f')
            ->once()
            ->andReturn($mockQb);

        $mockQb
            ->shouldReceive('innerJoin')->once()->with('f.feeTransactions', 'ft')->andReturnSelf()
            ->shouldReceive('innerJoin')->once()->with('f.feeType', 'ftp')->andReturnSelf()
            ->shouldReceive('innerJoin')->once()->with('ft.transaction', 't')->andReturnSelf()
            ->shouldReceive('addOrderBy')->once()->with('t.completedDate', 'DESC')->andReturnSelf()
            ->shouldReceive('addOrderBy')->once()->with('t.id', 'DESC')->andReturnSelf()
            ->shouldReceive('expr')
            ->andReturn(
                m::mock()
                    ->shouldReceive('eq')
                    ->with('f.licence', ':licence')
                    ->andReturn('cond1')
                    ->once()
                    ->shouldReceive('eq')
                    ->with('f.feeStatus', ':feeStatus')
                    ->andReturn('cond2')
                    ->once()
                    ->shouldReceive('eq')
                    ->with('ftp.feeType', ':feeType')
                    ->andReturn('cond3')
                    ->once()
                    ->getMock()
            )
            ->times(3)
            ->shouldReceive('andWhere')->with('cond1')->once()->andReturnSelf()
            ->shouldReceive('andWhere')->with('cond2')->once()->andReturnSelf()
            ->shouldReceive('andWhere')->with('cond3')->once()->andReturnSelf()
            ->shouldReceive('setParameter')->with('licence', $licenceId)->once()->andReturnSelf()
            ->shouldReceive('setMaxResults')->with(1)->once()->andReturnSelf()
            ->shouldReceive('setParameter')->with('feeType', RefDataEntity::FEE_TYPE_CONT)->once()->andReturnSelf()
            ->shouldReceive('setParameter')->with('feeStatus', FeeEntity::STATUS_PAID)->once()->andReturnSelf()
            ->getMock();

        $mockQb->shouldReceive('getQuery->getResult')->with(Query::HYDRATE_OBJECT)->once()->andReturn(['foo']);

        $this->assertSame('foo', $this->sut->fetchLatestPaidContinuationFee($licenceId));
    }
}
