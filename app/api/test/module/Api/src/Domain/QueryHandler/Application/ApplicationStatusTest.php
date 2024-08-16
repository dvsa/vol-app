<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\DataService\ApplicationStatus
 */
class ApplicationStatusTest extends QueryHandlerTestCase
{
    /** @var QueryHandler\DataService\ApplicationStatus  */
    protected $sut;

    /** @var  Repository\DataService | m\MockInterface */
    private $mockDataSrvRepo;

    public function setUp(): void
    {
        $this->sut = new QueryHandler\DataService\ApplicationStatus();

        $this->mockDataSrvRepo = $this->mockRepo('DataService', Repository\DataService::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        /** @var QueryInterface | m\MockInterface $query */
        $query = m::mock(QueryInterface::class);

        $mockEntity = m::mock();
        $mockEntity->shouldReceive('serialize')->with([])->andReturn('EXPECT');

        $this->mockDataSrvRepo
            ->shouldReceive('fetchApplicationStatus')->with($query)->once()->andReturn([$mockEntity, $mockEntity]);

        $actual = $this->sut->handleQuery($query);

        static::assertEquals(
            [
                'result' => ['EXPECT', 'EXPECT'],
                'count' => 2,
            ],
            $actual
        );
    }
}
