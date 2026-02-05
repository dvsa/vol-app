<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\CreatePoliceDocument;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateAndStoreCmd;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreatePoliceDocument as CreatePoliceDocumentCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class CreatePoliceDocumentTest
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CreatePoliceDocumentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreatePoliceDocument();
        $this->mockRepo('Publication', PublicationRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 11;
        $docCategory = 22;
        $docSubCategory = 113;
        $publicationNo = 12345;
        $docDescription = 'doc description';
        $docDescriptionPolice = $docDescription . ' ' . $publicationNo . ' police version';
        $generatedDocId = 2345;
        $previousDocId = 33;

        $docGenerateCmdData = [
            'template' => $previousDocId,
            'query' => [
                'id' => $id
            ],
            'description'   => $docDescriptionPolice,
            'category'      => $docCategory,
            'subCategory'   => $docSubCategory,
            'isExternal'    => true,
        ];

        $command = CreatePoliceDocumentCmd::create(['id' => $id]);

        $publicationEntity = m::mock(PublicationEntity::class);
        $publicationEntity->shouldReceive('getDocTemplate->getDescription')->once()->andReturn($docDescription);
        $publicationEntity->shouldReceive('getDocTemplate->getCategory->getId')->once()->andReturn($docCategory);
        $publicationEntity->shouldReceive('getDocTemplate->getSubCategory->getId')->once()->andReturn($docSubCategory);
        $publicationEntity->shouldReceive('getDocument->getId')->once()->andReturn($previousDocId);
        $publicationEntity->shouldReceive('getPublicationNo')->once()->andReturn($publicationNo);
        $publicationEntity->shouldReceive('getId')->once()->andReturn($id);

        $generatedDocResult = new Result();
        $generatedDocResult->addId('document', $generatedDocId);

        $this->expectedSideEffect(GenerateAndStoreCmd::class, $docGenerateCmdData, $generatedDocResult);

        $this->repoMap['Publication']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($publicationEntity);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'document' => $generatedDocId,
        ];

        $this->assertEquals($expected, $result->getIds());
    }
}
