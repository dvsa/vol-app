<?php

declare(strict_types=1);

/**
 * GenerateTest.php
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Publication;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Publication as PublicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\Publication\Generate;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as DocTemplateEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\Publication\Generate as GenerateCommand;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateDocCommand;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreateNextPublication as CreateNextPublicationCmd;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Class GenerateTest
 */
class GenerateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Generate();
        $this->mockRepo('Publication', PublicationRepo::class);
        $this->mockRepo('PublicationLink', PublicationLinkRepo::class);
        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [PublicationEntity::PUB_GENERATED_STATUS];

        $this->references = [
            DocumentEntity::class => [
                2345 => m::mock(DocumentEntity::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $id = 11;
        $docTemplateId = 685;
        $docCategory = 11;
        $docSubCategory = 113;
        $docDescription = 'doc description';
        $pubNo = 12345;
        $pubStatus = new RefData(PublicationEntity::PUB_GENERATED_STATUS);
        $pubType = 'A&D';
        $generatedDocId = 2345;
        $nextPublicationId = 3456;

        $docGenerateCmdData = [
            'template' => $docTemplateId,
            'query' => [
                'publicationId' => $id,
                'pubType' => $pubType
            ],
            'description'   => $docDescription . ' ' . $pubNo . ' generated',
            'category'      => $docCategory,
            'subCategory'   => $docSubCategory,
            'isExternal'    => true,
        ];

        $command = GenerateCommand::create(['id' => $id]);

        $document = new DocumentEntity($docTemplateId);

        $category = new CategoryEntity();
        $category->setId($docCategory);

        $subCategory = new SubCategoryEntity();
        $subCategory->setId($docSubCategory);

        $docTemplate = new DocTemplateEntity();
        $docTemplate->setDocument($document);
        $docTemplate->setCategory($category);
        $docTemplate->setSubCategory($subCategory);
        $docTemplate->setDescription($docDescription);

        $publicationEntity = m::mock(PublicationEntity::class)->makePartial();
        $publicationEntity->setId($id);
        $publicationEntity->setDocTemplate($docTemplate);
        $publicationEntity->setPubStatus($pubStatus);
        $publicationEntity->setPubType($pubType);
        $publicationEntity->setPublicationNo($pubNo);
        $publicationEntity->shouldReceive('generate')
            ->once()
            ->with(
                $this->references[DocumentEntity::class][$generatedDocId],
                $this->refData[PublicationEntity::PUB_GENERATED_STATUS]
            )
            ->andReturnSelf();

        /** @var PublicationLinkEntity $publicationLinkEntityA */
        $publicationLinkEntityA = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLinkEntityA->setId(1);

        /** @var PublicationLinkEntity $publicationLinkEntityB */
        $publicationLinkEntityB = m::mock(PublicationLinkEntity::class)->makePartial();
        $publicationLinkEntityB->setId(2);

        $docGenerationResult = new Result();
        $docGenerationResult->addId('document', $generatedDocId);
        $docGenerationResult->addMessage('Document created');

        $this->expectedSideEffect(GenerateDocCommand::class, $docGenerateCmdData, $docGenerationResult);

        $createNextPublicationResult = new Result();
        $createNextPublicationResult->addId('created_publication', $nextPublicationId);
        $this->expectedSideEffect(CreateNextPublicationCmd::class, ['id' => $id], $createNextPublicationResult);

        $this->repoMap['Publication']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($publicationEntity);

        $this->repoMap['Publication']->shouldReceive('save')
            ->once()
            ->with(m::type(PublicationEntity::class));

        $ineligibleLinks = [$publicationLinkEntityA, $publicationLinkEntityB];

        $this->repoMap['PublicationLink']
            ->shouldReceive('fetchIneligiblePublicationLinks')
            ->once()
            ->andReturn($ineligibleLinks);

        $this->repoMap['PublicationLink']
            ->shouldReceive('save')
            ->once()
            ->with($publicationLinkEntityA)
            ->shouldReceive('save')
            ->once()
            ->with($publicationLinkEntityB);

        $this->repoMap['Publication']
            ->shouldReceive('fetchById')
            ->once()
            ->with($nextPublicationId);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'document' => $generatedDocId,
                'generated_publication' => $id,
                'created_publication' => $nextPublicationId
            ],
            'messages' => [
                'Document created',
                'Publication was generated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
