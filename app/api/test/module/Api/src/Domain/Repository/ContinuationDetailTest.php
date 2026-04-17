<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ContinuationDetail as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as Entity;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * ContinuationDetailTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationDetailTest extends RepositoryTestCase
{
    /** @var m\MockInterface|Repo */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchForLicence(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        static::assertEquals(['RESULTS'], $this->sut->fetchForLicence(95));

        $dateTime = new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime();
        $year = $dateTime->format('Y');
        $futureYear = (int) $year + 4;
        $month = $dateTime->format('n');
        $pastYear = (int) $year - 4;

        $expectedQuery = <<<EOT
BLAH AND m.licence = [[95]]
    AND l.status IN [[["lsts_valid","lsts_curtailed","lsts_suspended"]]]
    AND (c.month >= [[$month]] AND c.year = [[$year]])
        OR (c.year > [[$year]] AND c.year < [[$futureYear]])
        OR (c.month <= [[$month]] AND c.year = [[$futureYear]])
        OR (c.month <= [[$month]] AND c.year = [[$year]])
        OR (c.year > [[$pastYear]] AND c.year < [[$year]])
        OR (c.month >= [[$month]] AND c.year = [[$pastYear]])
    AND m.status IN ([[["con_det_sts_printed","con_det_sts_acceptable","con_det_sts_unacceptable"]]])
EOT;
        // Expected query has be formatted to make it readable, need to make it non formatted for assertion
        // remove new lines
        $expectedQuery = str_replace("\n", ' ', $expectedQuery);
        // remove indentation
        $expectedQuery = str_replace("  ", '', $expectedQuery);

        static::assertEquals($expectedQuery, $this->query);
    }

    public function testFetchOngoingForLicence(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getSingleResult')
                ->andReturn('RESULT')
                ->getMock()
        );
        static::assertEquals('RESULT', $this->sut->fetchOngoingForLicence(95));

        $expectedQuery = 'BLAH AND m.licence = [[95]] AND (m.status = [[con_det_sts_acceptable]] '
            . 'OR (m.status != [[con_det_sts_complete]] AND m.isDigital = 1))';

        static::assertEquals($expectedQuery, $this->query);
    }

    /**
     * Test fetchChecklistReminders
     */
    public function testFetchChecklistReminders(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->twice()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();

        $mockQb
            ->shouldReceive('select')->once()->andReturnSelf()
            ->shouldReceive('innerJoin')->with('m.continuation', 'c')->once()->andReturnSelf()
            ->shouldReceive('innerJoin')->with('m.licence', 'l')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('l.status', 'ls')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('l.goodsOrPsv', 'lgp')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('l.organisation', 'lo')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('l.fees', 'lf')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('lf.feeType', 'lfft')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('lfft.feeType', 'lfftft')->once()->andReturnSelf()
            ->shouldReceive('leftJoin')->with('lf.feeStatus', 'lffs')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('l.status', ':licenceStatuses')->once()->andReturn('conditionLic');
        $mockQb->shouldReceive('andWhere')->with('conditionLic')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with(
                'licenceStatuses',
                [
                    LicenceEntity::LICENCE_STATUS_VALID,
                    LicenceEntity::LICENCE_STATUS_CURTAILED,
                    LicenceEntity::LICENCE_STATUS_SUSPENDED
                ]
            )
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->neq')->with('m.status', ':status')->once()->andReturn('unit_CondStatus');
        $mockQb->shouldReceive('andWhere')->with('unit_CondStatus')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', Entity::STATUS_PREPARED);

        $mockQb->shouldReceive('expr->eq')->with('m.received', 0)->once()->andReturn('conditionReceived');
        $mockQb->shouldReceive('andWhere')->with('conditionReceived')->once()->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')->with('m.isDigital', 0)->once()->andReturn('conditionIsDigital');
        $mockQb->shouldReceive('andWhere')->with('conditionIsDigital')->once()->andReturnSelf();

        $this->queryBuilder->shouldReceive('filterByIds')->with([1])->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')
            ->with('c.month', ':month')
            ->once()
            ->andReturn('conditionMonth');
        $mockQb->shouldReceive('andWhere')->with('conditionMonth')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('month', 1)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')
            ->with('c.year', ':year')
            ->once()
            ->andReturn('conditionYear');
        $mockQb->shouldReceive('andWhere')->with('conditionYear')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('year', 2016)
            ->once()
            ->andReturnSelf();

        $mockQb->expects('expr->in')->with('l.trafficArea', ':trafficAreas')->andReturn('conditionTa');
        $mockQb->expects('andWhere')->with('conditionTa');
        $mockQb->expects('setParameter')->with('trafficAreas', ['A', 'B']);

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($mockQb);

        $fee1 = m::mock()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getFeeType')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('getId')
                        ->andReturn(FeeTypeEntity::FEE_TYPE_APP)
                        ->once()
                        ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $fee2 = m::mock()
            ->shouldReceive('getFeeType')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getFeeType')
                    ->andReturn(
                        m::mock()
                            ->shouldReceive('getId')
                            ->andReturn(FeeTypeEntity::FEE_TYPE_CONT)
                            ->once()
                            ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->once()
            ->shouldReceive('getFeeStatus')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getId')
                    ->andReturn(FeeEntity::STATUS_OUTSTANDING)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $fees = [$fee1, $fee2];

        $mockEntity1 = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getFees')
                    ->andReturn($fees)
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $mockEntity2 = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn(
                m::mock()
                    ->shouldReceive('getFees')
                    ->andReturn([])
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $expected = new ArrayCollection();
        $expected->add($mockEntity2);

        $mockQb->shouldReceive('getQuery->getResult')
            ->with(\Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn([$mockEntity1, $mockEntity2]);

        static::assertEquals($expected, $this->sut->fetchChecklistReminders(['A', 'B'], 1, 2016, [1]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('statusProvider')]
    public function testFetchDetails(mixed $method, mixed $allowEmail): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('status', 's')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.status', 'ls')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.organisation', 'lo')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.licenceType', 'lt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.goodsOrPsv', 'lg')->once()->andReturnSelf();

        $mockQb->shouldReceive('orderBy')->with('l.licNo', 'ASC')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('c.id', ':continuationId')->once()->andReturn('conditionContId');
        $mockQb->shouldReceive('andWhere')->with('conditionContId')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('continuationId', 1)->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('l.status', ':licenceStatuses')->once()->andReturn('conditionLicSt');
        $mockQb->shouldReceive('andWhere')->with('conditionLicSt')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licenceStatuses', ['st'])->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('l.licNo', ':licNo')->once()->andReturn('conditionLicNo');
        $mockQb->shouldReceive('andWhere')->with('conditionLicNo')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licNo', 'ln')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('lo.allowEmail', $allowEmail)->once()->andReturn('conditionMethod');
        $mockQb->shouldReceive('andWhere')->with('conditionMethod')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.status', ':status')->once()->andReturn('conditionStatus');
        $mockQb->shouldReceive('andWhere')->with('conditionStatus')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('status', 'st')->once()->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('getQuery->getResult')
            ->with(\Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->once()
            ->andReturn(['result']);

        static::assertEquals(
            $this->sut->fetchDetails(1, ['st'], 'ln', $method, 'st'),
            ['result']
        );
    }

    public static function statusProvider(): array
    {
        return [
            [Entity::METHOD_EMAIL, 1],
            [Entity::METHOD_POST, 0]
        ];
    }

    public function testFetchWithLicence(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('status', 's')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.licenceType', 'lt')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('l.goodsOrPsv', 'lg')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('byId')->with(1)->once()->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('getQuery->getSingleResult')
            ->once()
            ->andReturn(['result']);

        static::assertEquals(
            $this->sut->fetchWithLicence(1),
            ['result']
        );
    }

    public function testFetchLicenceIdsForContinuationAndLicences(): void
    {
        $mockQb = m::mock(QueryBuilder::class);

        $this->queryBuilder->shouldReceive('modifyQuery')->with($mockQb)->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('withRefdata')->once()->andReturnSelf();
        $this->queryBuilder->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->in')->with('m.licence', ':licences')->once()->andReturn('licences');
        $mockQb->shouldReceive('andWhere')->with('licences')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licences', [222, 333])->once()->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.continuation', ':continuation')->once()->andReturn('continuation');
        $mockQb->shouldReceive('andWhere')->with('continuation')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('continuation', 111)->once()->andReturnSelf();

        $this->em
            ->shouldReceive('getRepository->createQueryBuilder')
            ->with('m')
            ->once()
            ->andReturn($mockQb);

        $mockQb->shouldReceive('getQuery->getResult')
            ->with(\Doctrine\ORM\Query::HYDRATE_ARRAY)
            ->once()
            ->andReturn([['licence' => ['id' => 123]]]);

        static::assertEquals(
            $this->sut->fetchLicenceIdsForContinuationAndLicences(111, [222, 333]),
            [123]
        );
    }

    public function testCreateContinuationDetails(): void
    {
        $query = m::mock();
        $query->shouldReceive('executeInsert')->once()->with([1], false, 'status', 2);

        $this->dbQueryService->shouldReceive('get')->with('Continuations\CreateContinuationDetails')->andReturn($query);

        $this->sut->createContinuationDetails([1], false, 'status', 2);
    }

    public function testFetchListForDigitalReminders(): void
    {
        $qb = $this->createMockQb('BLAH');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('continuation', 'c')->once()->andReturnSelf()
            ->shouldReceive('with')->with('licence', 'l')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery->getResult')->with()->once()->andReturn('RESULT');

        static::assertEquals('RESULT', $this->sut->fetchListForDigitalReminders(54));

        $fromDate = (new DateTime())->format('Y-m-d');
        $toDate = (new DateTime())->add(new \DateInterval('P54D'))->format('Y-m-d');
        $expectedQuery = 'BLAH AND l.status IN ["lsts_valid","lsts_curtailed","lsts_suspended"] ' .
            'AND l.expiryDate >= [[' . $fromDate . ']] ' .
            'AND l.expiryDate <= [[' . $toDate . ']] ' .
            'AND m.status NOT IN ["con_det_sts_complete"] ' .
            'AND c.month = MONTH(l.expiryDate) ' .
            'AND c.year = YEAR(l.expiryDate) ' .
            'AND m.digitalNotificationSent = 1 ' .
            'AND m.digitalReminderSent = 0';

        static::assertEquals($expectedQuery, $this->query);
    }
}
