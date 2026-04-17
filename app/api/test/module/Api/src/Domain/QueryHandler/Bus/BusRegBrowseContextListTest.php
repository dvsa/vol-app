<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\BusRegBrowseView as BusRegBrowseViewRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\BusRegBrowseContextList;
use Dvsa\Olcs\Transfer\Query\Bus\BusRegBrowseContextList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * BusRegBrowseContextList test
 */
class BusRegBrowseContextListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusRegBrowseContextList();
        $this->mockRepo('BusRegBrowseView', BusRegBrowseViewRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $context = 'context';

        $query = Qry::create(
            [
                'context' => $context
            ]
        );

        // Use reflection to set the value of context property
        $reflectionProperty = new \ReflectionProperty(Qry::class, 'context');
        $reflectionProperty->setValue($query, $context);

        $this->repoMap['BusRegBrowseView']
            ->shouldReceive('fetchDistinctList')
            ->with($context)
            ->andReturn(['foo'])
            ->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(1);

        $this->assertEquals(
            [
                'result' => ['foo'],
                'count' => 1
            ],
            $this->sut->handleQuery($query, Query::HYDRATE_OBJECT)
        );
    }
}
