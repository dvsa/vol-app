<?php

declare(strict_types=1);

/**
 * LegacyOffenceList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\LegacyOffenceList;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\LegacyOffence as LegacyOffenceRepo;
use Dvsa\Olcs\Transfer\Query\Cases\LegacyOffenceList as Qry;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Mockery as m;

/**
 * LegacyOffenceList Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class LegacyOffenceListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LegacyOffenceList();
        $this->mockRepo('LegacyOffence', LegacyOffenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $mockLegacyOffence = m::mock(BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->once()
            ->getMock();

        $this->repoMap['LegacyOffence']->shouldReceive('fetchList')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn([$mockLegacyOffence]);

        $this->repoMap['LegacyOffence']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(1);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($result['count'], 1);
        $this->assertEquals($result['result'], [['foo' => 'bar']]);
    }
}
