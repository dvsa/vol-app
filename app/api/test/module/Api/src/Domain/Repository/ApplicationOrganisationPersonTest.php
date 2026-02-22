<?php

declare(strict_types=1);

/**
 * ApplicationOrganisationPersonTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOrganisationPerson as Repo;

/**
 * ApplicationOrganisationPersonTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ApplicationOrganisationPersonTest extends RepositoryTestCase
{
    /**
     * @var Repo
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(Repo::class);
    }

    public function testFetchListForOrganisation(): void
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
        $this->assertEquals(['RESULTS'], $this->sut->fetchListForApplication(34));

        $expectedQuery = '[QUERY] AND m.application = [[34]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForApplicationAndPerson(): void
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

        $this->assertEquals('RESULTS', $this->sut->fetchForApplicationAndPerson(34, 76));

        $expectedQuery = '[QUERY] AND m.application = [[34]] AND m.person = [[76]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForApplicationAndPersonNotFound(): void
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
                ->andReturn([])
                ->getMock()
        );

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->sut->fetchForApplicationAndPerson(34, 76);
    }

    public function testFetchForApplicationAndOriginalPerson(): void
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

        $this->assertEquals('RESULTS', $this->sut->fetchForApplicationAndOriginalPerson(34, 76));

        $expectedQuery = '[QUERY] AND m.application = [[34]] AND m.originalPerson = [[76]]';
        $this->assertEquals($expectedQuery, $this->query);
    }

    public function testFetchForApplicationAndOriginalPersonNotFound(): void
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
                ->andReturn([])
                ->getMock()
        );

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->sut->fetchForApplicationAndOriginalPerson(34, 76);
    }

    public function testDeleteForPerson(): void
    {
        $qb = $this->createMockQb('[DELETE]');

        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('delete')->with(\Dvsa\Olcs\Api\Entity\Application\ApplicationOrganisationPerson::class, 'm')
            ->once()->andReturnSelf();
        $qb->shouldReceive('getQuery->execute')->with()->once();

        $person = new \Dvsa\Olcs\Api\Entity\Person\Person();
        $person->setId(99);

        $this->sut->deleteForPerson($person);

        $expectedQuery = '[DELETE] ' .
            'AND m.person = [[Dvsa\Olcs\Api\Entity\Person\Person]] ' .
            'OR m.originalPerson = [[Dvsa\Olcs\Api\Entity\Person\Person]]';
        $this->assertEquals($expectedQuery, $this->query);
    }
}
