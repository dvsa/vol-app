<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Document\Upload;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\File\MimeNotAllowedException;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Document\Upload::class)]
class UploadTest extends AbstractCommandHandlerTestCase
{
    public const BODY = 'expect_body';
    public const IDENTIFIER = '/some/identifier.pdf';
    public const USER_ID = 7001;

    /** @var Upload */
    protected $sut;

    /** @var  m\MockInterface */
    private $mockUploader;

    public function setUp(): void
    {
        $this->sut = new Upload();

        $this->mockRepo('Document', Document::class);

        $this->mockUploader = m::mock(ContentStoreFileUploader::class);
        $this->mockedSmServices['FileUploader'] = $this->mockUploader;

        $this->mockedSmServices['DocumentNamingService'] = m::mock(NamingService::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $refData = new Entity\System\RefData();

        $org = new Entity\Organisation\Organisation();
        $org->setId(6001);
        $org->setType(clone $refData);
        $org->setTradingNames(new ArrayCollection(['TrN']));

        $this->references = [
            Entity\Licence\Licence::class => [
                111 => new Entity\Licence\Licence($org, $refData),
            ],
            Entity\Application\Application::class => [
                222 => m::mock(Entity\Application\Application::class)
            ],
            Entity\Cases\Cases::class => [
                333 => m::mock(Entity\Cases\Cases::class)
            ],
            Entity\Tm\TransportManager::class => [
                444 => m::mock(Entity\Tm\TransportManager::class)
            ],
            Entity\Bus\BusReg::class => [
                555 => m::mock(Entity\Bus\BusReg::class)
            ],
            Entity\Organisation\Organisation::class => [
                666 => m::mock(Entity\Organisation\Organisation::class),
            ],
            Entity\Surrender::class => [
                777 => m::mock(Entity\Surrender::class),
            ],
            Entity\Permits\IrhpApplication::class => [
                888 => m::mock(Entity\Permits\IrhpApplication::class),
            ],
        ];

        $this->categoryReferences = [
            11 => m::mock(Entity\System\Category::class)
        ];

        $this->subCategoryReferences = [
            22 => m::mock(Entity\System\SubCategory::class)
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpLinkedEntity')]
    public function testHandleCommand(mixed $key, mixed $id, mixed $entityClass): void
    {
        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.pdf',
            'category' => 11,
            'subCategory' => 22,
            'isExternal' => 1,
            'description' => 'description',
            'user' => self::USER_ID,
            'shouldUploadOnly' => false,
            $key => $id,
        ];

        $command = TransferCmd\Document\Upload::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'description',
                'pdf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[$entityClass][$id]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockUploader
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($fileName, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $fileName);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        //  mock document creation
        $result = new Result();
        $result->addMessage('CreateDocumentSpecific');
        $data = [
            'identifier' => self::IDENTIFIER,
            'size' => strlen(self::BODY),
            'filename' => self::IDENTIFIER,
            'description' => 'description',
            'isExternal' => 1,
            'user' => self::USER_ID,
        ];
        $this->expectedSideEffect(DomainCmd\Document\CreateDocumentSpecific::class, $data, $result);

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
                'CreateDocumentSpecific'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpLinkedEntity(): array
    {
        return [
            [
                'key' => 'licence',
                'id' => 111,
                'entityClass' => Entity\Licence\Licence::class,
            ],
            [
                'application',
                222,
                Entity\Application\Application::class
            ],
            [
                'case',
                333,
                Entity\Cases\Cases::class
            ],
            [
                'transportManager',
                444,
                Entity\Tm\TransportManager::class
            ],
            [
                'busReg',
                555,
                Entity\Bus\BusReg::class
            ],
            [
                'irfoOrganisation',
                666,
                Entity\Organisation\Organisation::class
            ],
            [
                'surrender',
                777,
                Entity\Surrender::class
            ],
            [
                'irhpApplication',
                888,
                Entity\Permits\IrhpApplication::class,
            ]
        ];
    }

    public function testHandleCommandFileAndIsExternalNull(): void
    {
        $gzBody = gzcompress(self::BODY);

        $vfs = vfsStream::setup('temp');
        $tmpFilePath = vfsStream::newFile('stream.zip')->withContent($gzBody)->at($vfs)->url();

        $expectMimeType = 'application/zlib';

        $data = [
            'content' => [
                'tmp_name' => $tmpFilePath,
                'type' => $expectMimeType,
            ],
            'filename' => 'fileName.xxml',
            'licence' => 111,
            'category' => 11,
            'subCategory' => 22,
            'isExternal' => null,
            'user' => self::USER_ID,
            'shouldUploadOnly' => false,
        ];

        $command = TransferCmd\Document\Upload::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'fileName',
                'xxml',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Licence\Licence::class][111]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockUploader
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($fileName, DsFile $file) use ($expectMimeType) {
                    static::assertSame(self::IDENTIFIER, $fileName);

                    static::assertEquals(self::BODY, gzuncompress($file->getContent()));
                    static::assertEquals($expectMimeType, $file->getMimeType());

                    $file->setIdentifier(self::IDENTIFIER);
                    return $file;
                }
            );

        //  mock document creation
        $result = new Result();
        $result->addMessage('CreateDocument');
        $data = [
            'identifier' => self::IDENTIFIER,
            'size' => strlen($gzBody),
            'filename' => self::IDENTIFIER,
            'description' => 'fileName',
            'user' => self::USER_ID,
        ];
        $this->expectedSideEffect(DomainCmd\Document\CreateDocument::class, $data, $result);

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
                'CreateDocument'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests EBSR doc upload throws exception if file isn't zip
     */
    public function testHandleCommandInvalidEbsrMime(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.pdf',
            'licence' => 111,
            'category' => 11,
            'subCategory' => 22,
            'isExternal' => 1,
            'isEbsrPack' => true,
            'user' => 1,
        ];

        $command = TransferCmd\Document\Upload::create($data);

        $this->mockedSmServices['DocumentNamingService']->shouldReceive('generateName')
            ->once()
            ->with(
                'fileName',
                'pdf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Licence\Licence::class][111]
            )
            ->andReturn(self::IDENTIFIER);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandInvalidMime(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.pdf',
            'licence' => 111,
            'category' => 11,
            'subCategory' => 22,
            'isExternal' => 1,
            'user' => 1,
        ];

        $command = TransferCmd\Document\Upload::create($data);

        $this->mockedSmServices['DocumentNamingService']->shouldReceive('generateName')
            ->once()
            ->with(
                'fileName',
                'pdf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Licence\Licence::class][111]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockUploader
            ->shouldReceive('upload')
            ->andThrow(MimeNotAllowedException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandError(): void
    {
        $this->expectException(\Exception::class);

        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.pdf',
            'licence' => 111,
            'category' => 11,
            'subCategory' => 22,
            'isExternal' => 1,
            'user' => 1,
        ];

        $command = TransferCmd\Document\Upload::create($data);

        $this->mockedSmServices['DocumentNamingService']->shouldReceive('generateName')
            ->once()
            ->with(
                'fileName',
                'pdf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Licence\Licence::class][111]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockUploader
            ->shouldReceive('upload')
            ->andThrow(new \Exception('any error'));

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithAdditionalCopy(): void
    {
        $data = [
            'content' => base64_encode(self::BODY),
            'filename' => 'fileName.pdf',
            'category' => 11,
            'subCategory' => 22,
            'isExternal' => 1,
            'description' => 'description',
            'user' => self::USER_ID,
            'shouldUploadOnly' => false,
            'transportManager' => 444,
            'licence' => 111,
            'application' => 222,
            'additionalCopy' => true,
            'additionalEntities' => ['application', 'licence']
        ];

        $command = TransferCmd\Document\Upload::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->twice()
            ->with(
                'description',
                'pdf',
                $this->categoryReferences[11],
                $this->subCategoryReferences[22],
                $this->references[Entity\Tm\TransportManager::class][444]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockUploader
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($fileName, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $fileName);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            )
            ->twice();

        //  mock document creation
        $result = new Result();
        $result->addMessage('CreateDocumentSpecific');
        $data = [
            'identifier' => self::IDENTIFIER,
            'size' => strlen(self::BODY),
            'filename' => self::IDENTIFIER,
            'description' => 'description',
            'isExternal' => 1,
            'user' => self::USER_ID,
            'transportManager' => 444
        ];
        $data1 = [
            'identifier' => self::IDENTIFIER,
            'size' => strlen(self::BODY),
            'filename' => self::IDENTIFIER,
            'description' => 'description',
            'isExternal' => 1,
            'user' => self::USER_ID,
            'application' => 222,
            'licence' => 111,
        ];
        $this->expectedSideEffect(DomainCmd\Document\CreateDocumentSpecific::class, $data, $result);
        $this->expectedSideEffect(DomainCmd\Document\CreateDocumentSpecific::class, $data1, $result);

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                0 => 'File uploaded',
                1 => 'File uploaded',
                2 => 'CreateDocumentSpecific',
                3 => 'CreateDocumentSpecific'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
