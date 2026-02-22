<?php

declare(strict_types=1);

/**
 * Stay Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Hearing;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Hearing\Stay;
use Dvsa\Olcs\Api\Domain\Repository\Stay as StayRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\Stay as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Stay Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class StayTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Stay();
        $this->mockRepo('Stay', StayRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Stay']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }
}
