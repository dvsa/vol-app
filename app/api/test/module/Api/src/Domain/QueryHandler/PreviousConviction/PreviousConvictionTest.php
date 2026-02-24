<?php

declare(strict_types=1);

/**
 * Previous Conviction Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\PreviousConviction;

use Dvsa\Olcs\Api\Domain\QueryHandler\PreviousConviction\PreviousConviction;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\PreviousConviction as PreviousConvictionRepo;
use Dvsa\Olcs\Transfer\Query\PreviousConviction\PreviousConviction as Qry;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;

/**
 * Previous Conviction Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PreviousConvictionTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PreviousConviction();
        $this->mockRepo('PreviousConviction', PreviousConvictionRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockPreviousConviction = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $this->repoMap['PreviousConviction']
            ->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockPreviousConviction);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
