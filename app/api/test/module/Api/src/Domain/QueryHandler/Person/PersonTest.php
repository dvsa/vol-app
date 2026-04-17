<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Person;

use Dvsa\Olcs\Api\Domain\QueryHandler\Person\Person as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Person\Person as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * PersonTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PersonTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Person', \Dvsa\Olcs\Api\Domain\Repository\Person::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockPerson = m::mock(\Dvsa\Olcs\Api\Entity\Person\Person::class)->makePartial();
        $mockPerson->shouldReceive('serialize')->with(
            ['disqualifications']
        )->once()->andReturn(['foo' => 'bar']);

        $this->repoMap['Person']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockPerson);

        $this->assertEquals(['foo' => 'bar'], $this->sut->handleQuery($query)->serialize());
    }
}
