<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bookmark;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bookmark\LicencePsvDiscCountNotCeased;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PublicationLinkBundle as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as Entity;

/**
 * LicencePsvDiscCountNotCeasedTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicencePsvDiscCountNotCeasedTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new LicencePsvDiscCountNotCeased();
        $this->mockRepo('Licence', Repo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1102]);

        /** @var Entity $entity */
        $entity = m::mock(Entity::class);
        $entity->shouldReceive('serialize')->with([])->once()->andReturn(['id' => 1102]);
        $entity->shouldReceive('getPsvDiscsNotCeasedCount')->with()->once()->andReturn(321);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($entity);

        $this->assertEquals(
            ['id' => 1102, 'notCeasedPsvDiscCount' => 321],
            $this->sut->handleQuery($query)
        );
    }

    public function testHandleQueryNotFound(): void
    {
        $query = Qry::create(['id' => 1102]);

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andThrow(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $this->assertEquals(
            null,
            $this->sut->handleQuery($query)
        );
    }
}
