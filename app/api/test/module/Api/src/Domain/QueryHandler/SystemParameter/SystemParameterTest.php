<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\SystemParameter;

use Dvsa\Olcs\Api\Domain\QueryHandler\Result;
use Dvsa\Olcs\Api\Domain\QueryHandler\SystemParameter\SystemParameter as QueryHandler;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SysParamEntity;
use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameter as Query;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Mockery as m;

/**
 * @see QueryHandler
 */
class SystemParameterTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);
        $this->mockedSmServices[CacheEncryption::class] = m::mock(CacheEncryption::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $id = 'param';
        $query = Query::create(['id' => $id]);
        $result = [
            'result' => ['foo'],
            'count' => 1,
        ];

        $this->mockedSmServices[CacheEncryption::class]->expects('hasCustomItem')
            ->with(CacheEncryption::SYS_PARAM_IDENTIFIER, $id)
            ->andReturnFalse();

        $mockSystemParameter = m::mock(SysParamEntity::class)
            ->expects('serialize')
            ->andReturn($result)
            ->getMock();

        $this->repoMap['SystemParameter']
            ->expects('fetchById')
            ->with($id)
            ->andReturn($mockSystemParameter)
            ->getMock();

        $this->mockedSmServices[CacheEncryption::class]->expects('setCustomItem')
            ->with(CacheEncryption::SYS_PARAM_IDENTIFIER, m::type(Result::class), $id);

        $this->assertSame(
            $result,
            $this->sut->handleQuery($query)->serialize()
        );
    }

    public function testHandleQueryFromCache(): void
    {
        $id = 'param';
        $cacheResult = new Result();

        $this->mockedSmServices[CacheEncryption::class]->expects('hasCustomItem')
            ->with(CacheEncryption::SYS_PARAM_IDENTIFIER, $id)
            ->andReturnTrue();

        $this->mockedSmServices[CacheEncryption::class]->expects('getCustomItem')
            ->with(CacheEncryption::SYS_PARAM_IDENTIFIER, $id)
            ->andReturn($cacheResult);

        $query = Query::create(['id' => $id]);

        $this->assertEquals(
            $cacheResult,
            $this->sut->handleQuery($query)
        );
    }
}
