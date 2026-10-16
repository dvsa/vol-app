<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\BusRegSearchView as Repo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\View\BusRegSearchView as Entity;
use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;

final class BusRegSearchViewTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchByRegNo(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame('RESULTS', $this->sut->fetchByRegNo('REG0001'));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE m.regNo = :regNo', $qb->getDQL());
        $this->assertSame('REG0001', $qb->getParameter('regNo')->getValue());
    }

    public function testFetchByRegNoNotFound(): void
    {
        $this->createRealQb()->willReturn([]);

        $this->expectException(NotFoundException::class);

        $this->sut->fetchByRegNo('REG0001');
    }

    public function testFetchActiveByLicence(): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchActiveByLicence(611));

        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.licId = :licence AND m.busRegStatus IN(:activeStatuses)',
            $qb->getDQL(),
        );
        $this->assertSame(611, $qb->getParameter('licence')->getValue());
        $this->assertSame(
            [BusReg::STATUS_NEW, BusReg::STATUS_VAR, BusReg::STATUS_REGISTERED, BusReg::STATUS_CANCEL],
            $qb->getParameter('activeStatuses')->getValue(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideContexts')]
    public function testFetchDistinctList(string $context, string $expectedSelect): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getContext')->andReturn($context);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList($query));

        $this->assertSame('SELECT DISTINCT ' . $expectedSelect . self::FROM, $qb->getDQL());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('provideContexts')]
    public function testFetchDistinctListWithOrganisationId(string $context, string $expectedSelect): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getContext')->andReturn($context);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList($query, 1));

        $this->assertSame(
            'SELECT DISTINCT ' . $expectedSelect . self::FROM . ' WHERE m.organisationId = :organisationId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('organisationId')->getValue());
    }

    /**
     * A local authority filter only applies when there is no organisation.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideContexts')]
    public function testFetchDistinctListWithLocalAuthorityId(string $context, string $expectedSelect): void
    {
        $qb = $this->createRealQb()->willReturn(['RESULTS']);

        $query = m::mock(QueryInterface::class);
        $query->shouldReceive('getContext')->andReturn($context);

        $this->assertSame(['RESULTS'], $this->sut->fetchDistinctList($query, null, 1));

        $this->assertSame(
            'SELECT DISTINCT ' . $expectedSelect . self::FROM . ' WHERE m.localAuthorityId = :localAuthorityId',
            $qb->getDQL(),
        );
        $this->assertSame(1, $qb->getParameter('localAuthorityId')->getValue());
    }

    public static function provideContexts(): \Iterator
    {
        yield 'licence' => ['licence', 'm.licId, m.licNo'];
        yield 'organisation' => ['organisation', 'm.organisationId, m.organisationName'];
        yield 'busRegStatus' => ['busRegStatus', 'm.busRegStatus, m.busRegStatusDesc'];
    }

    /**
     * Rows are grouped by id because the view can return several per registration, which would
     * otherwise hydrate to duplicate objects (OLCS-14215).
     */
    public function testApplyListFiltersGroupsById(): void
    {
        $qb = $this->createRealQb();

        $this->sut->applyListFilters($qb, SearchViewList::create(['licId' => '1234']));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.licId = :licId GROUP BY m.id',
            $qb->getDQL(),
        );
        $this->assertSame('1234', $qb->getParameter('licId')->getValue());
    }
}
