<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\QueryHandler\Document\BucketBrowserList;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\Transfer\Query\Document\BucketBrowserList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

final class BucketBrowserListTest extends QueryHandlerTestCase
{
    /** @var m\MockInterface|S3BucketBrowser */
    private $mockBrowser;

    public function setUp(): void
    {
        $this->sut = new BucketBrowserList();
        $this->mockBrowser = m::mock(S3BucketBrowser::class);

        $this->mockedSmServices = [
            S3BucketBrowser::class => $this->mockBrowser,
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser->getId')->andReturn(1);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $listing = [
            'prefix' => 'documents/',
            'folders' => ['documents/LICENSING/'],
            'objects' => [['key' => 'documents/x.pdf', 'size' => 10, 'lastModified' => null]],
            'nextContinuationToken' => 'tok',
            'isTruncated' => true,
        ];

        $this->mockBrowser->shouldReceive('listByPrefix')->with('documents/', null)->once()->andReturn($listing);

        $result = $this->sut->handleQuery(Qry::create(['prefix' => 'documents/']));

        $this->assertSame(['result' => $listing], $result);
    }

    public function testHandleQueryPassesContinuationToken(): void
    {
        $this->mockBrowser->shouldReceive('listByPrefix')->with('documents/', 'tok-1')->once()->andReturn([
            'prefix' => 'documents/', 'folders' => [], 'objects' => [], 'nextContinuationToken' => null, 'isTruncated' => false,
        ]);

        $this->sut->handleQuery(Qry::create(['prefix' => 'documents/', 'continuationToken' => 'tok-1']));
    }
}
