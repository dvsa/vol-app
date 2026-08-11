<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\QueryHandler\RetrievalLink\Download;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLink as RetrievalLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLinkDocument as RetrievalLinkDocumentRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLinkEvent as RetrievalLinkEventRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkDocument as MemberEntity;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Service\Retrieval\SessionGrantService;
use Dvsa\Olcs\Transfer\Query\RetrievalLink\Download as DownloadQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

final class DownloadTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Download();
        $this->mockRepo('RetrievalLinkDocument', RetrievalLinkDocumentRepo::class);
        $this->mockRepo('RetrievalLink', RetrievalLinkRepo::class);
        $this->mockRepo('RetrievalLinkEvent', RetrievalLinkEventRepo::class);

        $this->mockedSmServices = [
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
            // Real (final class can't be mocked); a gate=none download never uses it.
            SessionGrantService::class => new SessionGrantService('a-sufficiently-long-test-secret-value'),
            'config' => [],
        ];

        parent::setUp();
    }

    public function testReturnsPresignedUrlWhenStoreSupportsIt(): void
    {
        $document = m::mock(DocumentEntity::class);
        $document->shouldReceive('getIdentifier')->andReturn('documents/x.rtf');

        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getToken')->andReturn('tok');
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getGateMode')->andReturn('none');
        $link->shouldReceive('getSourceContext')->andReturn('publication:1');

        $member = m::mock(MemberEntity::class);
        $member->shouldReceive('getRetrievalLink')->andReturn($link);
        $member->shouldReceive('getMemberRef')->andReturn('mref');
        $member->shouldReceive('getDisplayFilename')->andReturn('pub.rtf');
        $member->shouldReceive('getDocument')->andReturn($document);

        $this->repoMap['RetrievalLinkDocument']->shouldReceive('fetchByMemberRef')->with('mref')->once()->andReturn($member);
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();
        $this->mockedSmServices['FileUploader']->shouldReceive('presignedGetUrl')
            ->with('documents/x.rtf', 300)->once()->andReturn('https://s3.example/presigned');

        $result = $this->sut->handleQuery(DownloadQry::create(['token' => 'tok', 'memberRef' => 'mref']));

        self::assertSame('https://s3.example/presigned', $result['presignedUrl']);
        self::assertSame('pub.rtf', $result['filename']);
    }
}
