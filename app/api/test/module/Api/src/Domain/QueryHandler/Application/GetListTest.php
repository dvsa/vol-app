<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as Repo;
use Dvsa\Olcs\Transfer\Query\Application\GetList as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * GetListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class);
        $application->shouldReceive('serialize')->with(['licence'])->once()->andReturn('SERIALIZED');

        $this->repoMap['Application']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->andReturn([$application]);
        $this->repoMap['Application']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result['result']);
        $this->assertSame('COUNT', $result['count']);
    }
}
