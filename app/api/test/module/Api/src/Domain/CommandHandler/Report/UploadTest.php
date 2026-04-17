<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Report;

use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Report\Upload;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Document;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\Document\NamingService;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\File\MimeNotAllowedException;
use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\Transfer\Command\Report\Upload as UploadCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use org\bovigo\vfs\vfsStream;
use LmcRbacMvc\Service\AuthorizationService;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Report\Upload::class)]
class UploadTest extends AbstractCommandHandlerTestCase
{
    public const FILENAME = 'fileName.csv';
    public const BODY = 'expect_body';
    public const IDENTIFIER = '/some/identifier.csv';
    public const USER_ID = 7001;

    /** @var Upload */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Upload();

        $this->mockRepo('Document', Document::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
            'DocumentNamingService' => m::mock(NamingService::class),
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->categoryReferences = [
            Category::CATEGORY_REPORT => m::mock(Category::class)
        ];

        $this->subCategoryReferences = [
            SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE => m::mock(SubCategory::class),
            SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER => m::mock(SubCategory::class),
            SubCategory::DOC_SUB_CATEGORY_POST_SCORING_EMAIL => m::mock(SubCategory::class),
        ];

        parent::initReferences();
    }

    public function testHandleCommandForCommunityLicenceBulkReprint(): void
    {
        $data = [
            'reportType' => RefData::REPORT_TYPE_COMM_LIC_BULK_REPRINT,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'community_licence_bulk_reprint',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                $this->subCategoryReferences[SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($identifier, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $identifier);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->shouldReceive('getId')
            ->andReturn(self::USER_ID);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->expectedSideEffect(
            CreateQueue::class,
            [
                'entityId' => null,
                'type' => Queue::TYPE_COMM_LIC_BULK_REPRINT,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode([
                    'identifier' => self::IDENTIFIER,
                    'user' => self::USER_ID,
                ]),
                'processAfterDate' => null,
            ],
            (new Result())->addMessage('Queue item created')
        );

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
                'Queue item created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForBulkEmail(): void
    {
        $data = [
            'reportType' => RefData::REPORT_TYPE_BULK_EMAIL,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
            'name' => 'template'
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'bulk_upload_email_send',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                $this->subCategoryReferences[SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($identifier, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $identifier);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->shouldReceive('getId')
            ->andReturn(self::USER_ID);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->expectedSideEffect(
            CreateQueue::class,
            [
                'entityId' => null,
                'type' => Queue::TYPE_EMAIL_BULK_UPLOAD,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode([
                    'identifier' => self::IDENTIFIER,
                    'user' => self::USER_ID,
                    'templateName' => $data['name']
                ]),
                'processAfterDate' => null,
            ],
            (new Result())->addMessage('Queue item created')
        );

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
                'Queue item created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForBulkLetter(): void
    {
        $data = [
            'reportType' => RefData::REPORT_TYPE_BULK_LETTER,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
            'templateSlug' => 'slug'
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'bulk_upload_letter_send',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                $this->subCategoryReferences[SubCategory::DOC_SUB_CATEGORY_REPORT_LETTER]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($identifier, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $identifier);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        /** @var UserEntity $user */
        $user = m::mock(UserEntity::class)->makePartial();
        $user->shouldReceive('getId')
            ->andReturn(self::USER_ID);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($user);

        $this->expectedSideEffect(
            CreateQueue::class,
            [
                'entityId' => null,
                'type' => Queue::TYPE_LETTER_BULK_UPLOAD,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode([
                    'identifier' => self::IDENTIFIER,
                    'user' => self::USER_ID,
                    'templateSlug' => $data['templateSlug']
                ]),
                'processAfterDate' => null,
            ],
            (new Result())->addMessage('Queue item created')
        );

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
                'Queue item created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForPostScoringEmail(): void
    {
        $data = [
            'reportType' => RefData::REPORT_TYPE_POST_SCORING_EMAIL,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'post_scoring_email_send',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                $this->subCategoryReferences[SubCategory::DOC_SUB_CATEGORY_POST_SCORING_EMAIL]
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($identifier, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $identifier);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        $this->expectedSideEffect(
            CreateQueue::class,
            [
                'entityId' => null,
                'type' => Queue::TYPE_POST_SCORING_EMAIL,
                'status' => Queue::STATUS_QUEUED,
                'options' => json_encode([
                    'identifier' => self::IDENTIFIER,
                ]),
                'processAfterDate' => null,
            ],
            (new Result())->addMessage('Queue item created')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
                'Queue item created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }


    public function testHandleCommandForGenericUpload(): void
    {
        $data = [
            'reportType' => null,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'uploaded_report',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                null
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andReturnUsing(
                function ($identifier, DsFile $file) {
                    static::assertSame(self::IDENTIFIER, $identifier);
                    static::assertEquals(self::BODY, $file->getContent());

                    $file->setIdentifier(self::IDENTIFIER);

                    return $file;
                }
            );

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandForContentFromStream(): void
    {
        $gzBody = gzcompress(self::BODY);

        $vfs = vfsStream::setup('temp');
        $tmpFilePath = vfsStream::newFile('stream.zip')->withContent($gzBody)->at($vfs)->url();

        $expectMimeType = 'application/zlib';

        $data = [
            'reportType' => null,
            'filename' => 'fileName.xxml',
            'content' => [
                'tmp_name' => $tmpFilePath,
                'type' => $expectMimeType,
            ],
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'uploaded_report',
                'xxml',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                null
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
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

        //  call
        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'identifier' => self::IDENTIFIER,
            ],
            'messages' => [
                'File uploaded',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandInvalidMime(): void
    {
        $this->expectException(ValidationException::class);

        $data = [
            'reportType' => null,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'uploaded_report',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                null
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andThrow(MimeNotAllowedException::class);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandError(): void
    {
        $this->expectException(\Exception::class);

        $data = [
            'reportType' => null,
            'filename' => self::FILENAME,
            'content' => base64_encode(self::BODY),
        ];

        $command = UploadCmd::create($data);

        $this->mockedSmServices['DocumentNamingService']
            ->shouldReceive('generateName')
            ->once()
            ->with(
                'uploaded_report',
                'csv',
                $this->categoryReferences[Category::CATEGORY_REPORT],
                null
            )
            ->andReturn(self::IDENTIFIER);

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('upload')
            ->andThrow(new \Exception('any error'));

        $this->sut->handleCommand($command);
    }
}
