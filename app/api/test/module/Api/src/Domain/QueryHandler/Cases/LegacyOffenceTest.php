<?php

declare(strict_types=1);

/**
 * LegacyOffence Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\LegacyOffence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LegacyOffence as LegacyOffenceRepo;
use Dvsa\Olcs\Transfer\Query\Cases\LegacyOffence as Qry;
use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;

/**
 * LegacyOffence Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class LegacyOffenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LegacyOffence();
        $this->mockRepo('LegacyOffence', LegacyOffenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockLegacyOffence = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['LegacyOffence']->shouldReceive('fetchCaseLegacyOffenceUsingId')
            ->with($query)
            ->andReturn($mockLegacyOffence);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
