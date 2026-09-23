<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\Query\FilterCollection;
use Dvsa\Olcs\Api\Domain\Repository\TransportManagerLicence as Repo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence as Entity;
use Dvsa\Olcs\Transfer\Query\TransportManagerLicence\GetList;
use Gedmo\SoftDeleteable\Filter\SoftDeleteableFilter;
use Mockery as m;

final class TransportManagerLicenceTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' tml';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchForLicence(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForLicence(7));

        $this->assertSame(
            'SELECT tml' . self::FROM . ' WHERE tml.licence = :licenceId',
            $qb->getDQL(),
        );
        $this->assertSame(7, $qb->getParameter('licenceId')->getValue());
    }

    /**
     * Removed transport managers are soft-deleted, so the filter has to be switched off for this
     * entity before the query can see them at all.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('removedTmProvider')]
    public function testFetchRemovedTmForLicence(bool $isSecondLetter, string $expectedExtra): void
    {
        $this->expectSoftDeleteableDisabledFor(Entity::class);

        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchRemovedTmForLicence(7, $isSecondLetter));

        $this->assertSame(
            'SELECT tml' . self::FROM
            . ' WHERE tml.licence = :licenceId AND tml.deletedDate IS NOT NULL'
            . ' AND tml.lastTmLetterDate IS NULL'
            . $expectedExtra
            . ' ORDER BY tml.deletedDate DESC',
            $qb->getDQL(),
        );
    }

    public static function removedTmProvider(): \Iterator
    {
        yield 'first letter' => [false, ' AND tml.lastTmFirstEmailDate IS NULL'];
        // The second letter only goes out 28 days after removal, and only if the first was sent.
        yield 'second letter' => [
            true,
            ' AND tml.lastTmFirstEmailDate IS NOT NULL AND tml.deletedDate <= :date28DaysAgo',
        ];
    }

    public function testFetchRemovedTmForLicenceSecondLetterLooksBack28Days(): void
    {
        $this->expectSoftDeleteableDisabledFor(Entity::class);

        $qb = $this->createRealQb()->willReturn([]);

        $this->sut->fetchRemovedTmForLicence(7, true);

        $this->assertSame(
            new \DateTime()->modify('-28 days')->format('Y-m-d') . ' 00:00:00',
            $qb->getParameter('date28DaysAgo')->getValue()->format('Y-m-d H:i:s'),
        );
    }

    public function testFetchWithContactDetailsByLicence(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchWithContactDetailsByLicence(7));

        // The explicit select() replaces the root select, so only the listed columns come back.
        $this->assertSame(
            'SELECT tml.id, tm.id as tmid, p.birthDate, p.forename, p.familyName, hcd.emailAddress'
            . self::FROM
            . ' INNER JOIN tml.transportManager tm INNER JOIN tm.homeCd hcd INNER JOIN hcd.person p'
            . ' WHERE tml.licence = :licenceId',
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('licenceStatusProvider')]
    public function testFetchForTransportManager(?array $statuses, string $expectedExtra): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchForTransportManager(3, $statuses));

        $this->assertSame(
            'SELECT tml, tmt, l, lo, ls, tm' . self::FROM
            . ' LEFT JOIN tml.tmType tmt LEFT JOIN tml.licence l LEFT JOIN l.organisation lo'
            . ' LEFT JOIN l.status ls LEFT JOIN tml.transportManager tm'
            . ' WHERE tml.transportManager = :transportManager'
            . $expectedExtra,
            $qb->getDQL(),
        );
        $this->assertSame(3, $qb->getParameter('transportManager')->getValue());
    }

    public static function licenceStatusProvider(): \Iterator
    {
        yield 'any status' => [null, ''];
        // The statuses are inlined into the IN() rather than bound.
        yield 'specific statuses' => [['lsts_valid'], " AND l.status IN('lsts_valid')"];
    }

    public function testFetchForResponsibilities(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getSingleResult')->andReturn('RESULT');

        $this->assertSame('RESULT', $this->sut->fetchForResponsibilities(1));

        $this->assertSame(
            'SELECT tml, l, lo, lst, tm, tmty, tmt' . self::FROM
            . ' LEFT JOIN tml.licence l LEFT JOIN l.organisation lo LEFT JOIN l.status lst'
            . ' LEFT JOIN tml.transportManager tm LEFT JOIN tm.tmType tmty LEFT JOIN tml.tmType tmt'
            . ' WHERE tml.id = :byId',
            $qb->getDQL(),
        );
    }

    public function testFetchByTmAndLicence(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByTmAndLicence(3, 7));

        $this->assertSame(
            'SELECT tml' . self::FROM
            . ' WHERE tml.transportManager = :tmId AND tml.licence = :licenceId',
            $qb->getDQL(),
        );
    }

    public function testFetchByLicence(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchByLicence(7));

        $this->assertSame(
            'SELECT tml, l, la, w0, tm' . self::FROM
            . ' LEFT JOIN tml.licence l LEFT JOIN l.applications la'
            . ' LEFT JOIN la.licenceType w0 LEFT JOIN tml.transportManager tm'
            . ' WHERE tml.licence = :licence',
            $qb->getDQL(),
        );
    }

    /**
     * Both filters use where() rather than andWhere(), so the second replaces the first — only
     * one of licence or transport manager can ever apply.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(array $data, string $expectedWhere, string $parameter): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, GetList::create($data));

        $this->assertSame('SELECT tml' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame(73, $qb->getParameter($parameter)->getValue());
    }

    public static function listFilterProvider(): \Iterator
    {
        yield 'licence' => [['licence' => 73], 'tml.licence = :licence', 'licence'];
        yield 'transport manager' => [
            ['transportManager' => 73],
            'tml.transportManager = :transportManager',
            'transportManager',
        ];
    }

    private function expectSoftDeleteableDisabledFor(string $entityClass): void
    {
        $filter = m::mock(SoftDeleteableFilter::class);
        $filter->expects('disableForEntity')->with($entityClass);

        $filters = m::mock(FilterCollection::class);
        $filters->shouldReceive('isEnabled')->with('soft-deleteable')->andReturnTrue();
        $filters->shouldReceive('getFilter')->with('soft-deleteable')->andReturn($filter);

        // disableSoftDeleteable() reaches for getFilters() twice: once to test, once to fetch.
        $this->em->shouldReceive('getFilters')->withNoArgs()->andReturn($filters);
    }
}
