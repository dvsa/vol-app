<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\PublicationLink;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLink as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\QueryHandler\Publication\PublicationLink
 */
class PublicationLinkTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PublicationLink();
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);
        $isNew = true;

        $publicationLink = m::mock(PublicationLinkEntity::class);
        $publicationLink
            ->shouldReceive('serialize')->once()->andReturn(['PublicationLink' => 'data'])
            ->shouldReceive('getPublication->isNew')->once()->andReturn($isNew);

        $this->repoMap['PublicationLink']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($publicationLink);

        /** @var \Dvsa\Olcs\Api\Domain\QueryHandler\Result $result */
        $result = $this->sut->handleQuery($query);

        $this->assertEquals(
            [
                'PublicationLink' => 'data',
                'isNew' => $isNew,
            ],
            $result->serialize()
        );
    }
}
