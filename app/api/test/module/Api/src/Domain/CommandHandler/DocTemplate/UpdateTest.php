<?php

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate\Update;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\Category as CategoryRepo;
use Dvsa\Olcs\Api\Domain\Repository\SubCategory as SubCategoryRepo;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplate as DocTemplateRepo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterType as LetterTypeRepo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as DocTemplateEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @covers \Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate\Update
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public const BODY = 'expect_body';
    public const IDENTIFIER = 'templates/fileName.rtf';
    public const USER_ID = 291;

    protected $sut;

    /** @var  m\MockInterface */
    private $mockUploader;

    public function setUp(): void
    {
        $this->sut = m::mock(Update::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $this->mockRepo('DocTemplate', DocTemplateRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('Category', CategoryRepo::class);
        $this->mockRepo('SubCategory', SubCategoryRepo::class);
        $this->mockRepo('User', UserRepo::class);
        $this->mockRepo('LetterType', LetterTypeRepo::class);

        $this->mockUploader = m::mock(ContentStoreFileUploader::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
            'DocumentNamingService' => m::mock(NamingService::class)
        ];

        parent::setUp();
    }

    protected function initReferences()
    {
        $this->references = [
            Entity\System\Category::class => [
                11 => m::mock(Entity\System\Category::class)
            ],
            Entity\System\SubCategory::class => [
                22 => m::mock(Entity\System\SubCategory::class)
            ],
            Entity\Doc\Document::class => [
                112 => m::mock(Entity\Doc\Document::class)
            ],
            Entity\User\User::class => [
                291 => m::mock(Entity\User\User::class)
            ],
            Entity\Letter\LetterType::class => [
                33 => m::mock(Entity\Letter\LetterType::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand()
    {
        $data = [
            'id' => 111,
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.rtf',
            'category' => 11,
            'subCategory' => 22,
            'templateFolder' => 'root',
            'description' => 'description',
            'suppressFromOp' => 'N',
            'isNi' => 'N',
            'letterType' => 33
        ];

        $command = TransferCmd\DocTemplate\Update::create($data);

        $docTemplate = m::mock(DocTemplateEntity::class)->makePartial();
        $docTemplate->setId(111);

        $this->repoMap['DocTemplate']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($docTemplate)
            ->once();

        $docTemplate->shouldReceive('updateMeta')
            ->with(
                m::type(Entity\System\Category::class),
                m::type(Entity\System\SubCategory::class),
                $command->getDescription(),
                $command->getIsNi(),
                $command->getSuppressFromOp(),
                m::type(Entity\Letter\LetterType::class)
            )
            ->once();

        $docTemplate->shouldReceive('setLetterType')
            ->with(m::type(Entity\Letter\LetterType::class))
            ->once();

        $document = m::mock(DocumentEntity::class)->makePartial();
        $document->setId(222);

        $docTemplate->shouldReceive('getDocument')
            ->andReturn($document)
            ->once();

        $result = new Result();
        $result->addId('document', 112);
        $result->addMessage('CreateDocumentSpecific');
        $data = [
            'id' => 222,
        ];
        $this->expectedSideEffect(TransferCmd\Document\DeleteDocument::class, $data, $result);

        $docTemplate->shouldReceive('updateDocument')
            ->with(m::type(DocumentEntity::class))
            ->once();

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->once()
            ->andReturnUsing(
                function ($fileName, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $fileName);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        $user = m::mock(Entity\User\User::class)->makePartial();
        $user->setId(self::USER_ID);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->sut->shouldReceive('getCurrentUser')
            ->andReturn(self::USER_ID);

        $result = new Result();
        $result->addId('document', 112);
        $result->addMessage('CreateDocumentSpecific');
        $data = [
            'identifier' => self::IDENTIFIER,
            'size' => strlen(self::BODY),
            'filename' => self::IDENTIFIER,
            'description' => 'description',
            'isExternal' => 0,
            'user' => self::USER_ID,
        ];
        $this->expectedSideEffect(DomainCmd\Document\CreateDocumentSpecific::class, $data, $result);

        $this->repoMap['DocTemplate']
            ->shouldReceive('save')
            ->once()
            ->with($docTemplate)
            ->andReturn($docTemplate);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
                'document' => 112,
                'docTemplate' => 111,
            ],
            'messages' => [
                 'CreateDocumentSpecific',
                 'File uploaded',
                 'CreateDocumentSpecific',
                 'DocTemplate Updated Successfully',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandNoContent()
    {
        $data = [
            'content' => null,
            'filename' => null,
            'category' => 11,
            'subCategory' => 22,
            'templateFolder' => 'root',
            'description' => 'new description',
            'suppressFromOp' => 'N',
            'isNi' => 'N'
        ];

        $command = TransferCmd\DocTemplate\Create::create($data);

        $docTemplate = m::mock(DocTemplateEntity::class)->makePartial();
        $docTemplate->setId(111);

        $this->repoMap['DocTemplate']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($docTemplate)
            ->once();

        $this->repoMap['DocTemplate']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(DocTemplateEntity::class))
            ->andReturn($docTemplate);

        $this->sut->handleCommand($command);
    }
}
