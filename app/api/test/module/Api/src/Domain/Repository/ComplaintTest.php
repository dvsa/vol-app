<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\DBAL\LockMode;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Complaint as ComplaintRepo;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class ComplaintTest extends RepositoryTestCase
{
    private const string REFDATA_JOINS = ' LEFT JOIN m.status w0 LEFT JOIN m.complaintType w1';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(ComplaintRepo::class, true);
    }

    public function testFetchUsingId(): void
    {
        $result = m::mock(Entity::class);

        $command = m::mock(QueryInterface::class);
        $command->shouldReceive('getId')->andReturn(111);
        $command->shouldReceive('getIsCompliance')->andReturn(false);

        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn([$result]);
        $this->em->expects('lock')->with($result, LockMode::OPTIMISTIC, 1);

        $this->assertSame($result, $this->sut->fetchUsingId($command, Query::HYDRATE_OBJECT, 1));

        $this->assertSame(
            'SELECT m, w0, w1, cd, w2, oc, w3 FROM ' . Entity::class . ' m' . self::REFDATA_JOINS
            . ' LEFT JOIN m.complainantContactDetails cd LEFT JOIN cd.person w2'
            . ' LEFT JOIN m.operatingCentres oc LEFT JOIN oc.address w3'
            . ' WHERE m.id = :byId AND m.isCompliance = :byIsCompliance',
            $qb->getDQL(),
        );
        $this->assertFalse($qb->getParameter('byIsCompliance')->getValue());
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, ccd, w0, oc, w1 FROM ' . Entity::class . ' m'
            . ' LEFT JOIN m.complainantContactDetails ccd LEFT JOIN ccd.person w0'
            . ' LEFT JOIN m.operatingCentres oc LEFT JOIN oc.address w1',
            $qb->getDQL(),
        );
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(
        array $criteria,
        string $expectedWhere,
        string $parameter,
        mixed $expectedValue,
        bool $joinsCase,
    ): void {
        $qb = $this->createRealQb();
        $previous = $this->newRealQb();
        $previous->select('other')->from(Entity::class, 'other');
        $this->queryBuilder->modifyQuery($previous);

        $query = m::mock(QueryInterface::class);
        foreach (['getCase', 'getIsCompliance', 'getLicence', 'getApplication'] as $getter) {
            $query->shouldReceive($getter)->with()->andReturn($criteria[$getter] ?? null);
        }

        $this->sut->applyListFilters($qb, $query);

        $this->assertStringEndsWith(' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame($expectedValue, $qb->getParameter($parameter)->getValue());
        $this->assertSame($joinsCase, str_contains($qb->getDQL(), 'LEFT JOIN m.case ca'));
        $this->assertSame('SELECT other FROM ' . Entity::class . ' other', $previous->getDQL());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'by case' => [['getCase' => 213], 'm.case = :byCase', 'byCase', 213, false];
        yield 'by compliance' => [
            ['getIsCompliance' => 324],
            'm.isCompliance = :isCompliance',
            'isCompliance',
            324,
            false,
        ];
        // The licence and application branches join case themselves.
        yield 'by licence' => [['getLicence' => 33], 'ca.licence = :licence', 'licence', 33, true];
        yield 'by application' => [
            ['getApplication' => 133],
            'ca.application = :application',
            'application',
            133,
            true,
        ];
    }

    public function testApplyListFiltersJoinsCaseOnceForLicenceAndApplication(): void
    {
        $qb = $this->createRealQb();
        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getCase', 'getIsCompliance')->andReturn(null);
        $query->shouldReceive('getLicence')->andReturn(33);
        $query->shouldReceive('getApplication')->andReturn(133);

        $this->sut->applyListFilters($qb, $query);

        $this->assertSame(1, substr_count($qb->getDQL(), 'LEFT JOIN m.case ca'));
        $this->assertStringContainsString('ca.licence = :licence', $qb->getDQL());
        $this->assertStringContainsString('ca.application = :application', $qb->getDQL());
        $this->compileDql($qb->getDQL());
    }
}
