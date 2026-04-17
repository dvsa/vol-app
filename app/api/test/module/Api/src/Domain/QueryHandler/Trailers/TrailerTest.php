<?php

declare(strict_types=1);

/**
 * Grace Period Test
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Trailers;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\Trailer\Trailer;
use Dvsa\Olcs\Api\Domain\Repository\Trailer as TrailerRepo;
use Dvsa\Olcs\Transfer\Query\Trailer\Trailer as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Grace Periods Test
 *
 * @author Joshua Curtis <josh.curtis@valtech.co.uk>
 */
class TrailerTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Trailer();
        $this->mockRepo('Trailer', TrailerRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockTrailer = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['Trailer']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockTrailer);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
