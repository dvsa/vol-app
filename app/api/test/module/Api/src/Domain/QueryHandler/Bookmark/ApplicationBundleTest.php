<?php

declare(strict_types=1);

/**
 * Application Bundle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\ApplicationBundle;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\ApplicationBundle as Qry;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Application Bundle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBundleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ApplicationBundle();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('Cases', Repository\Cases::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111, 'bundle' => ['foo' => ['bar']]]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('serialize')
            ->with(['foo' => ['bar']])
            ->andReturn(['id' => 111]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $this->assertEquals(['id' => 111], $this->sut->handleQuery($query));
    }

    public function testHandleQueryNotFound(): void
    {
        $query = Qry::create(['id' => 111, 'bundle' => ['foo' => ['bar']]]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->once()
            ->andThrow(NotFoundException::class);

        $this->assertEquals(null, $this->sut->handleQuery($query));
    }

    public function testHandleQueryForCase(): void
    {
        $query = Qry::create(['case' => 111, 'bundle' => ['foo' => ['bar']]]);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('serialize')
            ->with(['foo' => ['bar']])
            ->andReturn(['id' => 111]);

        $case = m::mock();
        $case->shouldReceive('getApplication')->with()->once()->andReturn($application);

        $this->repoMap['Cases']->shouldReceive('fetchById')->with(111)->once()
            ->andReturn($case);

        $this->assertEquals(['id' => 111], $this->sut->handleQuery($query));
    }

    public function testHandleQueryForCaseNotFound(): void
    {
        $query = Qry::create(['case' => 111, 'bundle' => ['foo' => ['bar']]]);

        $this->repoMap['Cases']->shouldReceive('fetchById')->with(111)->once()
            ->andThrow(NotFoundException::class);

        $this->assertEquals(null, $this->sut->handleQuery($query));
    }

    public function testHandleQueryNoParams(): void
    {
        $query = Qry::create([]);

        $this->assertEquals(null, $this->sut->handleQuery($query));
    }
}
