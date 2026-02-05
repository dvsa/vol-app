<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Cases;
use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\CasesWithOppositionDates as QueryHandler;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection as PublicationSectionEntity;
use Dvsa\Olcs\Transfer\Query\Cases\CasesWithOppositionDates as Qry;

/**
 * CasesWithOppositionDatesTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CasesWithOppositionDatesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Cases', Cases::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockApplication = m::mock(ApplicationEntity::class);
        $mockApplication->shouldReceive('getOutOfOppositionDate')->withNoArgs()->once()->andReturn('STRING');
        $mockApplication->shouldReceive('getOutOfRepresentationDate')->withNoArgs()->once()->andReturn('STRING');

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('getApplication')->withNoArgs()->andReturn($mockApplication);
        $mockCase->shouldReceive('serialize')->andReturn(['SERIALIZED']);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(
            [
                'SERIALIZED',
                'oooDate' => '',
                'oorDate' => '',
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryWithDates(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockApplication = m::mock(ApplicationEntity::class);
        $mockApplication->shouldReceive('getOutOfOppositionDate')->withNoArgs()->once()
            ->andReturn(new \DateTime('1996-07-27'));
        $mockApplication->shouldReceive('getOutOfRepresentationDate')->withNoArgs()->once()
            ->andReturn(new \DateTime('2002-10-02'));

        $mockCase = m::mock(CasesEntity::class);
        $mockCase->shouldReceive('getApplication')->withNoArgs()->andReturn($mockApplication);
        $mockCase->shouldReceive('serialize')->andReturn(['SERIALIZED']);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(
            [
                'SERIALIZED',
                'oooDate' => '1996-07-27T00:00:00+0000',
                'oorDate' => '2002-10-02T00:00:00+0000',
            ],
            $result->serialize()
        );
    }

    public function testHandleQueryFilterPublicationLinks(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockApplication = m::mock(ApplicationEntity::class)->makePartial();
        $mockApplication->initCollections();
        $mockApplication->shouldReceive('getOutOfOppositionDate')->withNoArgs()->once()->andReturnNull();
        $mockApplication->shouldReceive('getOutOfRepresentationDate')->withNoArgs()->once()->andReturnNull();

        $publicationSectionAppNew = new PublicationSectionEntity();
        $publicationSectionAppNew->setId(PublicationSectionEntity::APP_NEW_SECTION);

        $publicationLink1Id = 100;
        $publicationLink1 = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink1->setId($publicationLink1Id);
        $publicationLink1->setPublicationSection($publicationSectionAppNew);
        $mockApplication->getPublicationLinks()->add($publicationLink1);

        $publicationSectionVarNew = new PublicationSectionEntity();
        $publicationSectionVarNew->setId(PublicationSectionEntity::VAR_NEW_SECTION);

        $publicationLink2Id = 200;
        $publicationLink2 = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink2->setId($publicationLink2Id);
        $publicationLink2->setPublicationSection($publicationSectionVarNew);
        $mockApplication->getPublicationLinks()->add($publicationLink2);

        $publicationSectionBusNew = new PublicationSectionEntity();
        $publicationSectionBusNew->setId(PublicationSectionEntity::BUS_NEW_SECTION);

        $publicationLink3Id = 300;
        $publicationLink3 = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLink3->setId($publicationLink3Id);
        $publicationLink3->setPublicationSection($publicationSectionBusNew);
        $mockApplication->getPublicationLinks()->add($publicationLink3);

        $mockCase = m::mock(CasesEntity::class)->makePartial();
        $mockCase->initCollections();
        $mockCase->setApplication($mockApplication);

        $this->repoMap['Cases']->shouldReceive('fetchUsingId')->with($query)->andReturn($mockCase);

        $result = $this->sut->handleQuery($query);

        $serialized = $result->serialize();

        $this->assertSame('', $serialized['oooDate']);
        $this->assertSame('', $serialized['oorDate']);
        $this->assertSame(2, count($serialized['application']['publicationLinks']));
        $this->assertSame($publicationLink1Id, $serialized['application']['publicationLinks'][0]['id']);
        $this->assertSame($publicationLink2Id, $serialized['application']['publicationLinks'][1]['id']);
    }
}
