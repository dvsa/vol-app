<?php

declare(strict_types=1);

/**
 * ByApplication Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ByApplication;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Cases\ByApplication as Qry;
use Mockery as m;

/**
 * ByApplication Test
 */
class ByApplicationTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ByApplication();
        $this->mockRepo('Cases', CasesRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $applicationId = 7;
        $licenceId = 99;
        $query = Qry::create(
            [
                'application' => $applicationId,
                'order' => 'ASC',
                'sort' => 'caseType',
                'limit' => 25,
                'page' => 13

            ]
        );

        $mockResult = m::mock();

        $licence = m::mock()
            ->shouldReceive('getId')
            ->andReturn($licenceId)
            ->getMock();

        $application = m::mock()
            ->shouldReceive('getLicence')
            ->andReturn($licence)
            ->getMock();

        $application
            ->shouldReceive('getLicence')
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->andReturn($application);

        $this->repoMap['Cases']->shouldReceive('fetchList')
            ->with($query, m::type('integer'))
            ->andReturn([$mockResult]);

        $this->repoMap['Cases']->shouldReceive('fetchCount')
            ->with($query)
            ->andReturn(2);

        $this->queryHandler->shouldReceive('handleQuery')
            ->andReturnUsing(
                function ($dto) use ($mockResult, $query, $applicationId) {
                    $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\Cases\ByLicence::class, $dto);
                    $data = $dto->getArrayCopy();

                    $this->assertEquals($query->getSort(), $data['sort']);
                    $this->assertEquals($query->getOrder(), $data['order']);
                    $this->assertEquals($query->getPage(), $data['page']);
                    $this->assertEquals($query->getLimit(), $data['limit']);
                    $this->assertEquals($query->getApplication(), $applicationId);

                    return $mockResult;
                }
            );

        $this->assertEquals($mockResult, $this->sut->handleQuery($query));
    }
}
