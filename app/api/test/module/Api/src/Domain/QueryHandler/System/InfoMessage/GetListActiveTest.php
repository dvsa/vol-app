<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\System\InfoMessage;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\System\InfoMessage\GetListActive
 */
class GetListActiveTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler\System\InfoMessage\GetListActive();
        $this->mockRepo('SystemInfoMessage', Repository\SystemInfoMessage::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query\System\InfoMessage\GetList::create([]);

        $mockItem = ['unit_Item'];

        $this->repoMap['SystemInfoMessage']
            ->shouldReceive('fetchListActive')
            ->with($query)
            ->once()
            ->andReturn([$mockItem, $mockItem]);

        $actual = $this->sut->handleQuery($query);

        static::assertSame(
            [
                'result' => [$mockItem, $mockItem],
                'count' => 2,
            ],
            $actual
        );
    }
}
