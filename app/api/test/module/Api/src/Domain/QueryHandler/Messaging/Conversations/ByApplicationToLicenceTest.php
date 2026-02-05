<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging\Conversations;

use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Conversations\ByApplicationToLicence as Handler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByApplicationToLicence as Qry;
use Dvsa\Olcs\Transfer\Query\Messaging\Conversations\ByLicence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ByApplicationToLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->mockRepo(Repository\Application::class, Repository\Application::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(
            [
                'application' => 1,
            ],
        );

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getId')->once()->andReturn(2);
        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getLicence')->once()->andReturn($mockLicence);

        $this->repoMap[Repository\Application::class]->shouldReceive('fetchById')->andReturn($mockApplication);

        $this->queryHandler->shouldReceive('handleQuery')
                           ->with(m::on(function ($argument) {
                               $this->assertInstanceOf(ByLicence::class, $argument);
                               $this->assertEquals(
                                   2,
                                   $argument->getLicence(),
                                   'Expected licence ID used in proxy call to ByLicence to match licence returned from application',
                               );
                               return true;
                           }))
                           ->once()
                           ->andReturn(
                               [
                                   'result' => [
                                       [
                                           'application' => null,
                                       ],
                                   ],
                               ],
                           );

        $this->sut->handleQuery($query);
    }
}
