<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DocTemplate;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate\Create;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\Upload;
use Dvsa\Olcs\Api\Domain\Query\Document\ByDocumentStoreId;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\Category as CategoryRepo;
use Dvsa\Olcs\Api\Domain\Repository\SubCategory as SubCategoryRepo;
use Dvsa\Olcs\Api\Domain\Repository\DocTemplate as DocTemplateRepo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\Doc\DocTemplate as DocTemplateEntity;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\DocTemplate\Create::class)]
class CreateTest extends AbstractCommandHandlerTestCase
{
    public const BODY = 'expect_body';
    public const IDENTIFIER = 'templates/fileName.rtf';
    public const USER_ID = 291;

    /** @var Upload */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockUploader;

    public function setUp(): void
    {
        $this->sut = m::mock(Create::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $this->mockRepo('DocTemplate', DocTemplateRepo::class);
        $this->mockRepo('Document', DocumentRepo::class);
        $this->mockRepo('Category', CategoryRepo::class);
        $this->mockRepo('SubCategory', SubCategoryRepo::class);
        $this->mockRepo('User', UserRepo::class);

        $this->mockUploader = m::mock(ContentStoreFileUploader::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
            'DocumentNamingService' => m::mock(NamingService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
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
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.rtf',
            'category' => 11,
            'subCategory' => 22,
            'templateFolder' => 'root',
            'description' => 'description',
            'suppressFromOp' => 'N',
            'isNi' => 'N'
        ];

        $command = TransferCmd\DocTemplate\Create::create($data);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(ByDocumentStoreId::class))
            ->andReturn([]);

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

        $docTemplate = null;

        $this->repoMap['DocTemplate']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(DocTemplateEntity::class))
            ->andReturnUsing(
                function (DocTemplateEntity $docT) use (&$docTemplate) {
                    $docT->setId(111);
                    $docTemplate = $docT;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
                'document' => 112,
                'docTemplate' => 111,
            ],
            'messages' => [
                'File uploaded',
                'CreateDocumentSpecific',
                'DocTemplate Created Successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandIdentifierExists(): void
    {
        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.rtf',
            'category' => 11,
            'subCategory' => 22,
            'templateFolder' => 'root',
            'description' => 'description',
            'suppressFromOp' => 'N',
        ];

        $command = TransferCmd\DocTemplate\Create::create($data);

        $this->sut->shouldReceive('handleQuery')
            ->once()
            ->with(m::type(ByDocumentStoreId::class))
            ->andReturn([1]);

        $this->expectException(RuntimeException::class);
        $this->sut->handleCommand($command);
    }
}
