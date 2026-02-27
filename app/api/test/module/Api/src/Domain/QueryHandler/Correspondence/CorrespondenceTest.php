<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Correspondence;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Correspondence Test
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class CorrespondenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\Correspondence\Correspondence();
        $this->mockRepo('Correspondence', Repository\Correspondence::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $expect = ['SERIALIZED'];

        $query = Query\Correspondence\Correspondence::create(['id' => 1]);

        $mockEntity = m::mock(Entity\Organisation\CorrespondenceInbox::class)
            ->shouldReceive('serialize')
            ->once()
            ->with(['document', 'licence'])
            ->andReturn($expect)
            ->getMock();

        $this->repoMap['Correspondence']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockEntity);

        /** @var QueryHandler\Result $actual */
        $actual = $this->sut->handleQuery($query);

        static::assertInstanceOf(QueryHandler\Result::class, $actual);
        static::assertEquals($expect, $actual->serialize());
    }
}
