<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\PreviousConvictions as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Application\PreviousConvictions as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * PreviousConvictionsTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PreviousConvictionsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Application', \Dvsa\Olcs\Api\Domain\Repository\Application::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['id' => 1066]);

        $mock = \Mockery::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);
        $mock->shouldReceive('serialize')->with(['previousConvictions'])->once()->andReturn(['RESULT']);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mock);

        $this->assertSame(['RESULT'], $this->sut->handleQuery($query)->serialize());
    }
}
