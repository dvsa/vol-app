<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Messaging;

use Dvsa\Olcs\Api\Domain\QueryHandler\Messaging\Documents;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Query\Messaging\Documents as Qry;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

final class DocumentsTest extends QueryHandlerTestCase
{
    private const string CORRELATION_ID = 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa';
    private const int USER_ID = 7;

    public function setUp(): void
    {
        $this->sut = new Documents();
        $this->mockRepo(Repository\Conversation::class, Repository\Conversation::class);
        $this->mockRepo(Repository\Document::class, Repository\Document::class);

        $authService = m::mock(AuthorizationService::class);
        $authService->allows('isGranted')->with(Permission::INTERNAL_USER, null)->andReturn(true);
        $authService->allows('getIdentity->getUser->getId')->andReturn(self::USER_ID);

        $this->mockedSmServices = [
            CacheEncryption::class => m::mock(CacheEncryption::class),
            AuthorizationService::class => $authService,
        ];

        parent::setUp();
    }

    public function testHandleQueryReturnsOnlyCorrelatedDocuments(): void
    {
        $this->mockedSmServices[CacheEncryption::class]
            ->expects('getCustomItem')
            ->with(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, self::CORRELATION_ID)
            ->andReturn([11, 12]);

        $first = m::mock(Document::class);
        $first->expects('serialize')->andReturn(['id' => 12]);
        $second = m::mock(Document::class);
        $second->expects('serialize')->andReturn(['id' => 11]);

        $this->repoMap[Repository\Document::class]
            ->expects('fetchUnassignedByIdsForUser')
            ->with([11, 12], self::USER_ID)
            ->andReturn([$first, $second]);

        $result = $this->sut->handleQuery(Qry::create(['correlationId' => self::CORRELATION_ID]));

        $this->assertSame([['id' => 12], ['id' => 11]], $result);
    }

    public function testHandleQueryWithNothingUploaded(): void
    {
        $this->mockedSmServices[CacheEncryption::class]
            ->expects('getCustomItem')
            ->with(CacheEncryption::GENERIC_STORAGE_IDENTIFIER, self::CORRELATION_ID)
            ->andReturn(null);

        $this->repoMap[Repository\Document::class]
            ->expects('fetchUnassignedByIdsForUser')
            ->with([], self::USER_ID)
            ->andReturn([]);

        $this->assertSame([], $this->sut->handleQuery(Qry::create(['correlationId' => self::CORRELATION_ID])));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('noCorrelationIdProvider')]
    public function testHandleQueryWithoutACorrelationId(array $query): void
    {
        $this->mockedSmServices[CacheEncryption::class]->expects('getCustomItem')->never();

        $this->repoMap[Repository\Document::class]
            ->expects('fetchUnassignedByIdsForUser')
            ->with([], self::USER_ID)
            ->andReturn([]);

        $this->assertSame([], $this->sut->handleQuery(Qry::create($query)));
    }

    public static function noCorrelationIdProvider(): \Iterator
    {
        yield 'not given' => [[]];
        yield 'empty' => [['correlationId' => '']];
    }
}
