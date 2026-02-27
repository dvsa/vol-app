<?php

declare(strict_types=1);

/**
 * History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Processing;

use Dvsa\Olcs\Api\Domain\QueryHandler\Processing\History;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Processing\History as Qry;
use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class HistoryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new History();
        $this->mockRepo('EventHistory', Repository\EventHistory::class);
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('Cases', Repository\Cases::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = [
            'transportManager' => 1
        ];

        $query = Qry::create($data);

        $mockEventHistory = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->once()
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockEventHistory])
            ->once()
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(5)
            ->once();

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [['foo' => 'bar']],
            'count' => 5
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Test that the organisation gets added when querying an application
     */
    public function testHandleQueryApplication(): void
    {
        $data = [
            'application' => 32
        ];
        $organisation = m::mock();
        $organisation->shouldReceive('getId')->with()->once()->andReturn(99);
        $licence = m::mock();
        $licence->shouldReceive('getId')->with()->once()->andReturn(87);
        $licence->shouldReceive('getOrganisation')->with()->once()->andReturn($organisation);
        $application = m::mock();
        $application->shouldReceive('getLicence')->with()->once()->andReturn($licence);

        $query = Qry::create($data);
        $this->repoMap['Application']->shouldReceive('fetchById')->with(32)->once()->andReturn($application);

        $mockEventHistory = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockEventHistory])
            ->shouldReceive('fetchCount')
            ->andReturn(5);

        $this->sut->handleQuery($query);

        $this->assertSame(99, $query->getOrganisation());
        $this->assertSame(87, $query->getLicence());
    }

    /**
     * Test that the organisation gets added when querying a licence
     */
    public function testHandleQueryLicence(): void
    {
        $data = [
            'licence' => 32
        ];
        $organisation = m::mock();
        $organisation->shouldReceive('getId')->with()->once()->andReturn(99);
        $licence = m::mock();
        $licence->shouldReceive('getId')->with()->once()->andReturn(32);
        $licence->shouldReceive('getOrganisation')->with()->once()->andReturn($organisation);

        $query = Qry::create($data);
        $this->repoMap['Licence']->shouldReceive('fetchById')->with(32)->once()->andReturn($licence);

        $mockEventHistory = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockEventHistory])
            ->shouldReceive('fetchCount')
            ->andReturn(5);

        $this->sut->handleQuery($query);

        $this->assertSame(99, $query->getOrganisation());
    }

    /**
     * Test that the organisation gets added when querying a case attached to licence
     */
    public function testHandleQueryCaseLicence(): void
    {
        $data = [
            'case' => 32
        ];
        $organisation = m::mock();
        $organisation->shouldReceive('getId')->with()->once()->andReturn(99);
        $licence = m::mock();
        $licence->shouldReceive('getId')->with()->once()->andReturn(552);
        $licence->shouldReceive('getOrganisation')->with()->once()->andReturn($organisation);
        $case = m::mock();
        $case->shouldReceive('getLicence')->with()->twice()->andReturn($licence);

        $query = Qry::create($data);
        $this->repoMap['Cases']->shouldReceive('fetchById')->with(32)->once()->andReturn($case);

        $mockEventHistory = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockEventHistory])
            ->shouldReceive('fetchCount')
            ->andReturn(5);

        $this->sut->handleQuery($query);

        $this->assertSame(99, $query->getOrganisation());
        $this->assertSame(552, $query->getLicence());
    }

    /**
     * Test that the organisation gets added when querying a case attached to transport manager
     */
    public function testHandleQueryCaseTransportManager(): void
    {
        $data = [
            'case' => 32
        ];

        $transportManager = m::mock();
        $transportManager->shouldReceive('getId')->with()->once()->andReturn(46);

        $case = m::mock();
        $case->shouldReceive('getLicence')->with()->once()->andReturn(null);
        $case->shouldReceive('getTransportManager')->with()->twice()->andReturn($transportManager);

        $query = Qry::create($data);
        $this->repoMap['Cases']->shouldReceive('fetchById')->with(32)->once()->andReturn($case);

        $mockEventHistory = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['EventHistory']
            ->shouldReceive('disableSoftDeleteable')
            ->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockEventHistory])
            ->shouldReceive('fetchCount')
            ->andReturn(5);

        $this->sut->handleQuery($query);

        $this->assertSame(null, $query->getOrganisation());
        $this->assertSame(46, $query->getTransportManager());
    }
}
