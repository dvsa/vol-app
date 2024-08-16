<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cache;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cache\RecordList as Handler;
use Dvsa\Olcs\Api\Domain\Query\Cache\RecordList as Qry;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameterList;
use Mockery as m;

/**
 * Tests the cache handler calls the correct query (uses the system param list query as an example)
 *
 * @see Handler
 */
class RecordListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(Handler::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
        ];

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $cacheId = 'id-string';

        $queryParams = [
            'id' => $cacheId,
        ];

        $this->mockedSmServices[CacheEncryption::class]
            ->expects('getQueryFromCustomIdentifier')
            ->with($cacheId)
            ->andReturn(SystemParameterList::class);

        $queryResult = new Result();
        $this->expectedQuery(SystemParameterList::class, [], $queryResult);

        $query = Qry::create($queryParams);
        $this->assertEquals($queryResult, $this->sut->handleQuery($query));
    }
}
