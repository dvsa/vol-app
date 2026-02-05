<?php

declare(strict_types=1);

/**
 * PublishTest.php
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\Publish;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Transfer\Command\Publication\Publish as PublishCommand;
use Dvsa\Olcs\Api\Domain\Command\Email\SendPublication as SendPublicationEmailCmd;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreatePoliceDocument as CreatePoliceDocumentCmd;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class PublishTest
 */
class PublishTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Publish();
        $this->mockRepo('Publication', PublicationRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [PublicationEntity::PUB_PRINTED_STATUS];

        $this->references = [
            DocumentEntity::class => [
                2345 => m::mock(DocumentEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $pubId = 11;
        $generatedDocId = 2345;
        $data = ['id' => $pubId];
        $pubDate = '2015-12-25';
        $pubDateWithTime = $pubDate . ' 00:00:00';

        $policeCmdData = [
            'id' => $pubId,
            'isPolice' => 'Y'
        ];

        $nonPoliceCmdData = [
            'id' => $pubId,
            'isPolice' => 'N'
        ];

        $command = PublishCommand::create($data);

        $documentEntity = m::mock(DocumentEntity::class);

        $policeGenerationResult = new Result();
        $policeGenerationResult->addId('document', $generatedDocId);
        $policeGenerationResult->addMessage('Document created');

        $this->repoMap['Document']
            ->shouldReceive('fetchById')
            ->once()
            ->with($generatedDocId)
            ->andReturn($documentEntity);

        $publicationEntity = m::mock(PublicationEntity::class)->makePartial();
        $publicationEntity->setId($pubId);
        $publicationEntity->setPubStatus(new RefData(PublicationEntity::PUB_GENERATED_STATUS));
        $publicationEntity->shouldReceive('publish')
            ->once()
            ->with($this->refData[PublicationEntity::PUB_PRINTED_STATUS])
            ->andReturnSelf();
        $publicationEntity->shouldReceive('updatePublishedDocuments')->once()->with($documentEntity)->andReturnSelf();
        $publicationEntity->shouldReceive('getPubDate')->once()->andReturn($pubDate);

        $this->repoMap['Publication']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($publicationEntity);

        $this->repoMap['Publication']->shouldReceive('save')
            ->once()
            ->with(m::type(PublicationEntity::class))
            ->andReturnUsing(
                function (PublicationEntity $publicationEntity) use (&$savedPublication) {
                    $publicationEntity->setId(11);
                    $savedPublication = $publicationEntity;
                }
            );

        $this->expectedSideEffect(CreatePoliceDocumentCmd::class, ['id' => $pubId], $policeGenerationResult);

        $this->expectedEmailQueueSideEffect(
            SendPublicationEmailCmd::class,
            $policeCmdData,
            $pubId,
            new Result(),
            $pubDateWithTime
        );

        $this->expectedEmailQueueSideEffect(
            SendPublicationEmailCmd::class,
            $nonPoliceCmdData,
            $pubId,
            new Result(),
            $pubDateWithTime
        );

        $result = $this->sut->handleCommand($command);

        $expectedId = [
            'document' => $generatedDocId,
            'Publication' => $pubId
        ];

        $this->assertEquals($expectedId, $result->getIds());
    }
}
