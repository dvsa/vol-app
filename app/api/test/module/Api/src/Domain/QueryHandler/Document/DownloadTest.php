<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\Document\Download;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Laminas\Http\Response\Stream;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Document\Download::class)]
class DownloadTest extends QueryHandlerTestCase
{
    /** @var  m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(Download::class . '[download, setIsInline]')
            ->shouldAllowMockingProtectedMethods();

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];

        $this->mockRepo('Document', \Dvsa\Olcs\Api\Domain\Repository\Document::class);

        $this->mockedSmServices['config'] = [];
        $this->mockedSmServices['FileUploader'] = m::mock(ContentStoreFileUploader::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleQuery')]
    public function testHandleQuery(bool $isInternalUser, string $documentDescription, ?string $chosenFilename): void
    {
        $this->setupIsInternalUser($isInternalUser);
        $identifier = 20062016;

        $query = TransferQry\Document\Download::create(
            [
                'identifier' => $identifier,
                'isInline' => true,
            ]
        );

        $fileName = 'foo/bar/12345.pdf';

        $document = m::mock(Document::class);
        $document->expects('getIdentifier')->withNoArgs()->andReturn($fileName);
        $document->expects('getDescription')->withNoArgs()->times($isInternalUser ? 1 : 0)->andReturn($documentDescription);

        $this->repoMap['Document']
            ->expects('fetchById')
            ->with($identifier)
            ->andReturn($document);

        $download = m::mock(Stream::class);

        $this->sut
            ->shouldReceive('setIsInline')->once()->with(true)
            ->shouldReceive('download')->once()->with($fileName, null, $chosenFilename)->andReturn($download);

        $actual = $this->sut->handleQuery($query);

        static::assertEquals($download, $actual);
    }

    public static function dpTestHandleQuery(): array
    {
        return [
            [false, 'description', null],
            [true, 'description', 'description'],
            [true, '', null],
        ];
    }
}
