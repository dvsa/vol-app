<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\AbstractQuery;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson as OrganisationPersonRepo;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson::class)]
class OrganisationPersonTest extends RepositoryTestCase
{
    /** @var OrganisationPersonRepo  */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(OrganisationPersonRepo::class);
    }

    public function testFetchByOrgAndPerson(): void
    {
        /** @var Organisation $organisation */
        $organisation = m::mock(Organisation::class)->makePartial();
        $organisation->setId(123);

        /** @var Person $person */
        $person = m::mock(Person::class)->makePartial();
        $person->setId(321);

        $mockQb = $this->createMockQb('{QUERY}');

        $this->mockCreateQueryBuilder($mockQb);

        $mockQuery = m::mock();

        $mockQuery->shouldReceive('execute')
            ->once()
            ->shouldReceive('getResult')
            ->once()
            ->andReturn('Foo');

        $mockQb->shouldReceive('getQuery')
            ->andReturn($mockQuery);

        $this->assertEquals('Foo', $this->sut->fetchByOrgAndPerson($organisation, $person));

        $this->assertEquals('{QUERY} AND m.organisation = 123 AND m.person = 321', $this->query);
    }

    public function testFetchListForOrganisation(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForOrganisation(34));

        $expectedQuery = '[QUERY] INNER JOIN m.person p AND m.organisation = [[34]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchCountForOrganisation(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock(AbstractQuery::class)->shouldReceive('getSingleScalarResult')
                ->andReturn('RESULT')
                ->getMock()
        );
        $this->assertEquals('RESULT', $this->sut->fetchCountForOrganisation(34));

        $expectedQuery = '[QUERY] SELECT COUNT(m.person) INNER JOIN m.person p AND m.organisation = [[34]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForOrganisationAndPerson(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('person', 'p')->once()->andReturnSelf()
            ->shouldReceive('with')->with('p.title')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForOrganisationAndPerson(34, 98));

        $expectedQuery = '[QUERY] AND m.organisation = [[34]] AND m.person = [[98]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForPerson(): void
    {
        $qb = $this->createMockQb('[QUERY]');

        $this->mockCreateQueryBuilder($qb);

        $this->queryBuilder
            ->shouldReceive('modifyQuery')->with($qb)->once()->andReturnSelf()
            ->shouldReceive('withRefdata')->with()->once()->andReturnSelf()
            ->shouldReceive('with')->with('person', 'p')->once()->andReturnSelf()
            ->shouldReceive('with')->with('p.title')->once()->andReturnSelf();

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('execute')
                ->shouldReceive('getResult')
                ->andReturn(['RESULTS'])
                ->getMock()
        );
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForPerson(354));

        $expectedQuery = '[QUERY] AND m.person = [[354]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
