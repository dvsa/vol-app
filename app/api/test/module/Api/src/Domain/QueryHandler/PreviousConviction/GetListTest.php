<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\QueryHandler\PreviousConviction\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction as Repo;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Transfer\Query\PreviousConviction\GetList as Query;
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
        $this->mockRepo('PreviousConviction', Repo::class);
        $this->mockRepo('TransportManager', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['transportManager' => 1]);

        $tm = new TransportManager();

        $this->repoMap['TransportManager']->shouldReceive('fetchById')
            ->with(1)->andReturn($tm);

        $previousConviction = new \Dvsa\Olcs\Api\Entity\Application\PreviousConviction();
        $previousConviction->setId(74);

        $this->repoMap['PreviousConviction']->shouldReceive('fetchList')
            ->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)->andReturn([$previousConviction]);
        $this->repoMap['PreviousConviction']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(74, $result['result'][0]['id']);
        $this->assertSame('COUNT', $result['count']);
    }
}
