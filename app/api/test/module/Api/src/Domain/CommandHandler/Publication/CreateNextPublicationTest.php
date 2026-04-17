<?php

declare(strict_types=1);

/**
 * Create Next Publication Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\CreateNextPublication;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreateNextPublication as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as DocTemplateEntity;

/**
 * Create Next Publication Test
 */
class CreateNextPublicationTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateNextPublication();
        $this->mockRepo('Publication', PublicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [PublicationEntity::PUB_NEW_STATUS];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 4;
        $trafficAreaMock = m::mock(TrafficAreaEntity::class);
        $docTemplateMock = m::mock(DocTemplateEntity::class);
        $nextPublicationDate = new \DateTime('2015-12-25 00:00:00'); //14 days later
        $pubType = 'A&D';
        $publicationNo = 111;

        $publication = m::mock(PublicationEntity::class)->makePartial();
        $publication->shouldReceive('getTrafficArea')->once()->andReturn($trafficAreaMock);
        $publication->shouldReceive('getDocTemplate')->once()->andReturn($docTemplateMock);
        $publication->shouldReceive('getNextPublicationDate')->once()->andReturn($nextPublicationDate);
        $publication->shouldReceive('getPubType')->once()->andReturn($pubType);
        $publication->shouldReceive('getPublicationNo')->once()->andReturn($publicationNo);

        $command = Cmd::create(['id' => $id]);

        $this->repoMap['Publication']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($publication);

        $this->repoMap['Publication']->shouldReceive('save')
            ->once()
            ->with(m::type(PublicationEntity::class))
            ->andReturnUsing(
                function (PublicationEntity $publicationEntity) use (&$savedPublication) {
                    $publicationEntity->setId(4);
                    $savedPublication = $publicationEntity;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'created_publication' => $id,
            ],
            'messages' => [
                'Publication created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->refData[PublicationEntity::PUB_NEW_STATUS],
            $savedPublication->getPubStatus()
        );
        $this->assertEquals($trafficAreaMock, $savedPublication->getTrafficArea());
        $this->assertEquals($docTemplateMock, $savedPublication->getDocTemplate());
        $this->assertEquals($nextPublicationDate, $savedPublication->getPubDate());
        $this->assertEquals($pubType, $savedPublication->getPubType());
        $this->assertEquals($publicationNo + 1, $savedPublication->getPublicationNo());
    }
}
