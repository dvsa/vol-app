<?php

declare(strict_types=1);

/**
 * Change Of Entity Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ChangeOfEntity;

use Dvsa\Olcs\Api\Domain\QueryHandler\ChangeOfEntity\ChangeOfEntity;
use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\Repository\ChangeOfEntity as ChangeOfEntityRepo;
use Dvsa\Olcs\Api\Entity\Organisation\ChangeOfEntity as ChangeOfEntityEntity;
use Dvsa\Olcs\Transfer\Query\ChangeOfEntity\ChangeOfEntity as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Change Of Entity Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ChangeOfEntityTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ChangeOfEntity();
        $this->mockRepo('ChangeOfEntity', ChangeOfEntityRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $entity = m::mock(ChangeOfEntityEntity::class)->makePartial();
        $entity
            ->shouldReceive('serialize')
            ->andReturn(['foo']);

        $this->repoMap['ChangeOfEntity']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(Result::class, $result);

        $this->assertEquals(['foo'], $result->serialize());
    }
}
