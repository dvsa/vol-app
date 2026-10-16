<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Repository\TeamPrinter as Repo;
use Dvsa\Olcs\Api\Entity\PrintScan\TeamPrinter as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class TeamPrinterTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    /**
     * A printer assignment is keyed on the team plus a sub-category and a user, either of which
     * may be absent — so an absent one has to match IS NULL rather than be skipped, or the lookup
     * would find a more specific assignment than the caller asked for.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('detailsProvider')]
    public function testFetchByDetails(
        ?int $subCategory,
        ?int $user,
        string $expectedWhere,
        array $expectedParameters,
    ): void {
        $qb = $this->createRealQb()->willReturn(['result']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getSubCategory')->andReturn($subCategory);
        $query->shouldReceive('getUser')->andReturn($user);
        $query->shouldReceive('getTeam')->andReturn(3);

        $this->assertSame(['result'], $this->sut->fetchByDetails($query));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());

        foreach ($expectedParameters as $name => $expected) {
            $this->assertSame($expected, $qb->getParameter($name)->getValue());
        }
    }

    public static function detailsProvider(): \Iterator
    {
        yield 'a user in a sub-category' => [
            1,
            2,
            'm.subCategory = :subCategory AND m.user = :user AND m.team = :team',
            ['subCategory' => 1, 'user' => 2, 'team' => 3],
        ];
        yield 'the team default' => [
            null,
            null,
            'm.subCategory IS NULL AND m.user IS NULL AND m.team = :team',
            ['team' => 3],
        ];
    }

    public function testApplyListJoins(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListJoins($qb);

        $this->assertSame(
            'SELECT m, sc, scc, u, t, ucd, ucdp' . self::FROM
            . ' LEFT JOIN m.subCategory sc LEFT JOIN sc.category scc'
            . ' LEFT JOIN m.user u LEFT JOIN m.team t'
            . ' LEFT JOIN u.contactDetails ucd LEFT JOIN ucd.person ucdp',
            $qb->getDQL(),
        );
    }

    /**
     * The list is sorted by name, which for a user means forename then family name and for a
     * category means its parent then itself — neither is a column, so both are computed into a
     * HIDDEN alias to sort on.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('listFilterProvider')]
    public function testApplyListFilters(?int $team, string $expectedWhere, bool $expectTeamParameter): void
    {
        $qb = $this->createRealQb();

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getTeam')->andReturn($team);

        $this->assertNull($this->sut->applyListFilters($qb, $query));

        $this->assertSame(
            'SELECT m, CONCAT(ucdp.forename, ucdp.familyName) as HIDDEN userSort,'
            . ' CONCAT(scc.description, sc.subCategoryName) as HIDDEN catSort'
            . self::FROM
            . ' WHERE ' . $expectedWhere
            . ' ORDER BY t.name ASC, userSort ASC, catSort ASC',
            $qb->getDQL(),
        );

        if ($expectTeamParameter) {
            $this->assertSame($team, $qb->getParameter('team')->getValue());

            return;
        }

        $this->assertNull($qb->getParameter('team'));
    }

    public static function listFilterProvider(): \Iterator
    {
        // An assignment with neither a sub-category nor a user is the fallback row, not a listing.
        $notBothNull = 'NOT(sc.id IS NULL AND u.id IS NULL)';

        // Bracketed only when it is not the sole predicate.
        yield 'one team' => [1, 'm.team = :team AND (' . $notBothNull . ')', true];
        yield 'every team' => [null, $notBothNull, false];
    }
}
