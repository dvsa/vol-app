<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\OrganisationPerson;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\OrganisationPerson\GetSingle as QueryHandler;
use Dvsa\Olcs\Transfer\Query\OrganisationPerson\GetSingle as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * GetSingleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetSingleTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('OrganisationPerson', \Dvsa\Olcs\Api\Domain\Repository\OrganisationPerson::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Query::create(['QUERY']);

        $mockOrganisationPerson = m::mock(\Dvsa\Olcs\Api\Entity\Organisation\OrganisationPerson::class);
        $mockOrganisationPerson->shouldReceive('serialize')->with(
            ['person' => ['title']]
        )->once()->andReturn(['SERIALIZED']);

        $this->repoMap['OrganisationPerson']->shouldReceive('fetchUsingId')->with($query)
            ->andReturn($mockOrganisationPerson);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['SERIALIZED'], $result->serialize());
    }
}
