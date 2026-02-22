<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\OtherLicence;

use Dvsa\Olcs\Api\Domain\QueryHandler\OtherLicence\GetList as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\OtherLicence as Repo;
use Dvsa\Olcs\Transfer\Query\OtherLicence\GetList as Query;
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
        $this->mockRepo('OtherLicence', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $otherLicence = new \Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence();
        $otherLicence->setId(74);

        $this->repoMap['OtherLicence']->shouldReceive('fetchList')->with($query, \Doctrine\ORM\Query::HYDRATE_OBJECT)
            ->andReturn([$otherLicence]);
        $this->repoMap['OtherLicence']->shouldReceive('fetchCount')->with($query)->andReturn('COUNT');

        $result = $this->sut->handleQuery($query);

        $this->assertSame(74, $result['result'][0]['id']);
        $this->assertSame('COUNT', $result['count']);
    }
}
