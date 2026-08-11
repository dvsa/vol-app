<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\RetrievalLink;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\RetrievalLink\Resolve;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLink as RetrievalLinkRepo;
use Dvsa\Olcs\Api\Domain\Repository\RetrievalLinkEvent as RetrievalLinkEventRepo;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as RetrievalLinkEntity;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkDocument as MemberEntity;
use Dvsa\Olcs\Transfer\Query\RetrievalLink\Resolve as ResolveQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;

final class ResolveTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Resolve();
        $this->mockRepo('RetrievalLink', RetrievalLinkRepo::class);
        $this->mockRepo('RetrievalLinkEvent', RetrievalLinkEventRepo::class);

        $this->mockedSmServices = [
            'FileUploader' => m::mock(ContentStoreFileUploader::class),
        ];

        parent::setUp();
    }

    public function testResolveReturnsRedactedSummary(): void
    {
        $member = m::mock(MemberEntity::class);
        $member->shouldReceive('getMemberRef')->andReturn('mref');
        $member->shouldReceive('getDisplayFilename')->andReturn('pub.rtf');

        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getExpiresAt')->withNoArgs()->andReturn('2026-08-01T00:00:00+00:00');
        $link->shouldReceive('getGateMode')->andReturn('none');
        $link->shouldReceive('getDocuments')->andReturn(new ArrayCollection([$member]));
        $link->shouldReceive('getSourceContext')->andReturn('publication:1');

        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->with('tok')->once()->andReturn($link);
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();
        $this->mockedSmServices['FileUploader']->shouldReceive('supportsPresignedUrls')->andReturn(false);

        $result = $this->sut->handleQuery(ResolveQry::create(['token' => 'tok']));

        self::assertSame('none', $result['gateMode']);
        self::assertSame(1, $result['documentCount']);
        self::assertSame('mref', $result['documents'][0]['memberRef']);
        self::assertSame('pub.rtf', $result['documents'][0]['displayFilename']);
        // Redacted: no recipient email, no real document ids leak into the summary.
        self::assertArrayNotHasKey('recipientEmail', $result);
        self::assertArrayNotHasKey('documentId', $result['documents'][0]);
        self::assertSame('stream', $result['downloadStrategy']);
    }

    public function testReportsPresignedStrategyWhenStoreSupportsIt(): void
    {
        $member = m::mock(MemberEntity::class);
        $member->shouldReceive('getMemberRef')->andReturn('mref');
        $member->shouldReceive('getDisplayFilename')->andReturn('pub.rtf');

        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('+1 day'));
        $link->shouldReceive('getExpiresAt')->withNoArgs()->andReturn('2026-08-01T00:00:00+00:00');
        $link->shouldReceive('getGateMode')->andReturn('none');
        $link->shouldReceive('getDocuments')->andReturn(new ArrayCollection([$member]));
        $link->shouldReceive('getSourceContext')->andReturn('publication:1');

        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->once()->andReturn($link);
        $this->repoMap['RetrievalLinkEvent']->shouldReceive('save')->once();
        $this->mockedSmServices['FileUploader']->shouldReceive('supportsPresignedUrls')->andReturn(true);

        $result = $this->sut->handleQuery(ResolveQry::create(['token' => 'tok']));

        self::assertSame('presigned', $result['downloadStrategy']);
    }

    public function testUnknownTokenFailsSecure(): void
    {
        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->once()->andReturn(null);

        $this->expectException(NotFoundException::class);
        $this->sut->handleQuery(ResolveQry::create(['token' => 'nope']));
    }

    public function testExpiredLinkFailsSecure(): void
    {
        $link = m::mock(RetrievalLinkEntity::class);
        $link->shouldReceive('getRevokedAt')->with(true)->andReturn(null);
        $link->shouldReceive('getExpiresAt')->with(true)->andReturn(new \DateTime('-1 hour'));

        $this->repoMap['RetrievalLink']->shouldReceive('fetchByToken')->once()->andReturn($link);

        $this->expectException(NotFoundException::class);
        $this->sut->handleQuery(ResolveQry::create(['token' => 'expired']));
    }
}
