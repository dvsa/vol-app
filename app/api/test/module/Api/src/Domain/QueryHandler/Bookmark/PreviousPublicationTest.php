<?php

declare(strict_types=1);

/**
 * Previous Publication Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\PreviousPublication;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByPi as Qry;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as Entity;

/**
 * Previous Publication Test
 */
class PreviousPublicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PreviousPublication();
        $this->mockRepo('PublicationLink', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $trafficArea = 99;
        $pi = 88;
        $pubType = 'A&D';
        $publicationNo = 77;
        $bundle = [];

        $query = Qry::create(
            [
                'pi' => $pi,
                'pubType' => $pubType,
                'publicationNo' => $publicationNo,
                'trafficArea' => $trafficArea,
                'bundle' => $bundle
            ]
        );

        /** @var Entity $entity */
        $entity = m::mock(Entity::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['PublicationLink']->shouldReceive('fetchPreviousPublicationNo')
            ->with($query)
            ->andReturn($entity);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }

    public function testHandleQueryNull(): void
    {
        $trafficArea = 99;
        $pi = 88;
        $pubType = 'A&D';
        $publicationNo = 77;
        $bundle = [];

        $query = Qry::create(
            [
                'pi' => $pi,
                'pubType' => $pubType,
                'publicationNo' => $publicationNo,
                'trafficArea' => $trafficArea,
                'bundle' => $bundle
            ]
        );

        $this->repoMap['PublicationLink']->shouldReceive('fetchPreviousPublicationNo')
            ->with($query)
            ->andReturn(null);

        $this->assertNull($this->sut->handleQuery($query));
    }
}
