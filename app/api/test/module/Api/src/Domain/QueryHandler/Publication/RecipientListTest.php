<?php

declare(strict_types=1);

/**
 * RecipientList Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Mockery as m;
use Doctrine\ORM\Query as DoctrineQuery;
use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\RecipientList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Recipient as RecipientRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Transfer\Query\Publication\RecipientList as Qry;

/**
 * RecipientList Test
 */
class RecipientListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new RecipientList();
        $this->mockRepo('Recipient', RecipientRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $this->repoMap['Recipient']->shouldReceive('fetchList')
            ->with($query, DoctrineQuery::HYDRATE_OBJECT)
            ->andReturn(
                [
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['foo'])
                        ->getMock(),
                    m::mock(BundleSerializableInterface::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['bar'])
                        ->getMock()
                ]
            );

        $this->repoMap['Recipient']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(2, $result['count']);
        $this->assertEquals([['foo'], ['bar']], $result['result']);
    }
}
