<?php

declare(strict_types=1);

/**
 * Bus Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Bus;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Transfer\Query\Bus\BusReg as Qry;
use Dvsa\OlcsTest\Api\Entity\User as UserEntity;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Bus Test
 */
class BusTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Bus();
        $this->mockRepo('Bus', BusRepo::class);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(true);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $bus = m::mock(BusReg::class)->makePartial();
        $bus->shouldReceive('serialize')
            ->andReturn(['foo']);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($bus);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['foo'], $result->serialize());
    }
}
