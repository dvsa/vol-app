<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\HearingBundle;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\HearingBundle as Qry;
use Dvsa\Olcs\Api\Entity;

/**
 * Hearing Bundle Test
 */
class HearingBundleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new HearingBundle();
        $this->mockRepo('Hearing', Repository\Hearing::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['case' => 987, 'bundle' => ['foo' => ['bar']]]);

        $hearing = m::mock(Entity\Cases\Hearing::class)->makePartial();
        $hearing->shouldReceive('serialize')->with(['foo' => ['bar']])->once()->andReturn(['SERIALIZED']);

        $this->repoMap['Hearing']->shouldReceive('fetchOneByCase')->with(987)->once()->andReturn($hearing);

        $this->assertEquals(['SERIALIZED'], $this->sut->handleQuery($query));
    }

    public function testHandleQueryNotFound(): void
    {
        $query = Qry::create(['case' => 987, 'bundle' => ['foo' => ['bar']]]);

        $this->repoMap['Hearing']->shouldReceive('fetchOneByCase')->with(987)->once()
            ->andThrow(NotFoundException::class);

        $this->assertEquals(null, $this->sut->handleQuery($query));
    }
}
