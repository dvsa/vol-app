<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Surrender;

use Dvsa\Olcs\Api\Domain\QueryHandler\Surrender\OpenCases as QryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Cases;
use Dvsa\Olcs\Transfer\Query\Surrender\OpenCases;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class OpenCasesTest extends QueryHandlerTestCase
{
    protected $sut;
    public function setUp(): void
    {
        $this->sut = new QryHandler();
        $this->mockRepo('Cases', Cases::class);
        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = OpenCases::create(['id' => 1]);

        $expected = [
            m::mock(\Dvsa\Olcs\Api\Entity\Cases\Cases::class)
                ->shouldReceive('serialize')->once()->andReturn('foo')->getMock()
        ];

        $this->repoMap['Cases']->shouldReceive(
            'fetchOpenCasesForSurrender'
        )->andReturn($expected);

        $this->assertEquals([
            'count' => 1,
            'results' => ['foo']
        ], $this->sut->handleQuery($query));
    }
}
