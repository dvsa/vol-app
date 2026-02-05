<?php

declare(strict_types=1);

/**
 * SlaTargetDate Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\System;

use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Domain\QueryHandler\System\SlaTargetDate as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\SlaTargetDate as SlaTargetDateRepo;
use Dvsa\Olcs\Transfer\Query\System\SlaTargetDate as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * SlaTargetDate Query Handler Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class SlaTargetDateTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('SlaTargetDate', SlaTargetDateRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(
            [
                'entityType' => 'document',
                'entityId' => 100
            ]
        );

        $this->repoMap['SlaTargetDate']
            ->shouldReceive('fetchUsingEntityIdAndType')
            ->with($query->getEntityType(), $query->getEntityId())
            ->once()
            ->andReturn(
                m::mock(BundleSerializableInterface::class)
                    ->shouldReceive('serialize')
                    ->andReturn(['foo'])
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['foo'], $result->serialize());
    }
}
